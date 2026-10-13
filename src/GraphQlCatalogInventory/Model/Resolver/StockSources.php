<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogInventory\Model\Resolver;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;

/**
 * The source items of every product of the request in the enabled sources of the website's stock,
 * read in one search.
 */
class StockSources implements BatchResolverInterface
{
    public function __construct(
        private readonly StockByWebsiteIdResolverInterface $stockByWebsiteId,
        private readonly GetSourcesAssignedToStockOrderedByPriorityInterface $sourcesOfStock,
        private readonly SourceItemRepositoryInterface $sourceItemRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
    }

    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        $stockId = (int)$this->stockByWebsiteId->execute((int)$context->getExtensionAttributes()->getStore()->getWebsiteId())->getStockId();
        $sources = [];
        foreach ($this->sourcesOfStock->execute($stockId) as $source) {
            if ($source->isEnabled()) {
                $sources[$source->getSourceCode()] = $source;
            }
        }
        $skus = array_values(array_unique(array_map(static fn($request) => (string)$request->getValue()['model']->getSku(), $requests)));
        $items = [];
        if ($sources && $skus) {
            $criteria = $this->searchCriteriaBuilder
                ->addFilter(SourceItemInterface::SKU, $skus, 'in')
                ->addFilter(SourceItemInterface::SOURCE_CODE, array_keys($sources), 'in')
                ->create();
            foreach ($this->sourceItemRepository->getList($criteria)->getItems() as $item) {
                $items[$item->getSku()][$item->getSourceCode()] = $item;
            }
        }
        $response = new BatchResponse();
        foreach ($requests as $request) {
            $productItems = $items[(string)$request->getValue()['model']->getSku()] ?? [];
            $rows = [];
            foreach ($sources as $code => $source) {
                if (!isset($productItems[$code])) {
                    continue;
                }
                $extension = $source->getExtensionAttributes();
                $rows[] = [
                    'source_code' => $code,
                    'name' => (string)$source->getName(),
                    'quantity' => (float)$productItems[$code]->getQuantity(),
                    'in_stock' => (int)$productItems[$code]->getStatus() === SourceItemInterface::STATUS_IN_STOCK,
                    'is_pickup_location' => $extension && method_exists($extension, 'getIsPickupLocationActive') && $extension->getIsPickupLocationActive(),
                    'latitude' => $source->getLatitude() === null ? null : (float)$source->getLatitude(),
                    'longitude' => $source->getLongitude() === null ? null : (float)$source->getLongitude(),
                    'postcode' => $source->getPostcode(),
                    'city' => $source->getCity(),
                    'country_code' => $source->getCountryId(),
                ];
            }
            $response->addResponse($request, $rows);
        }

        return $response;
    }
}
