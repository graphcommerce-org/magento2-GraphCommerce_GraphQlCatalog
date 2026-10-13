<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogInventory\Test\Unit\Model\Resolver;

use GraphCommerce\GraphQlCatalogInventory\Model\Resolver\StockSources;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\TestCase;

class StockSourcesTest extends TestCase
{
    public function testTheItemsOfTheEnabledSourcesInPriorityOrder(): void
    {
        $stock = $this->createStub(StockInterface::class);
        $stock->method('getStockId')->willReturn(2);
        $stockByWebsite = $this->createMock(StockByWebsiteIdResolverInterface::class);
        $stockByWebsite->expects(self::once())->method('execute')->with(3)->willReturn($stock);
        $sources = $this->createStub(GetSourcesAssignedToStockOrderedByPriorityInterface::class);
        $sources->method('execute')->willReturn([$this->source('shop', true), $this->source('closed', false), $this->source('warehouse', true)]);
        $results = $this->createStub(SourceItemSearchResultsInterface::class);
        $results->method('getItems')->willReturn([$this->item('A', 'warehouse', 7, 1), $this->item('A', 'shop', 2, 0), $this->item('B', 'shop', 1, 1)]);
        $repository = $this->createMock(SourceItemRepositoryInterface::class);
        $repository->expects(self::once())->method('getList')->willReturn($results);
        $criteria = $this->createStub(SearchCriteriaBuilder::class);
        $criteria->method('addFilter')->willReturnSelf();
        $criteria->method('create')->willReturn($this->createStub(SearchCriteria::class));

        $requests = [$this->request('A'), $this->request('B'), $this->request('C')];
        $response = (new StockSources($stockByWebsite, $sources, $repository, $criteria))->resolve($this->context(), $this->createStub(Field::class), $requests);

        self::assertSame(['shop', 'warehouse'], array_column($response->findResponseFor($requests[0]), 'source_code'));
        self::assertSame([2.0, 7.0], array_column($response->findResponseFor($requests[0]), 'quantity'));
        self::assertSame([false, true], array_column($response->findResponseFor($requests[0]), 'in_stock'));
        self::assertSame('Shop shop', $response->findResponseFor($requests[1])[0]['name']);
        self::assertSame([], $response->findResponseFor($requests[2]));
    }

    private function source(string $code, bool $enabled): SourceInterface
    {
        $source = $this->createStub(SourceInterface::class);
        $source->method('getSourceCode')->willReturn($code);
        $source->method('isEnabled')->willReturn($enabled);
        $source->method('getName')->willReturn('Shop ' . $code);
        $source->method('getCountryId')->willReturn('NL');
        return $source;
    }

    private function item(string $sku, string $source, float $quantity, int $status): SourceItemInterface
    {
        $item = $this->createStub(SourceItemInterface::class);
        $item->method('getSku')->willReturn($sku);
        $item->method('getSourceCode')->willReturn($source);
        $item->method('getQuantity')->willReturn($quantity);
        $item->method('getStatus')->willReturn($status);
        return $item;
    }

    private function request(string $sku): BatchRequestItemInterface
    {
        $product = $this->createStub(Product::class);
        $product->method('getSku')->willReturn($sku);
        $request = $this->createStub(BatchRequestItemInterface::class);
        $request->method('getValue')->willReturn(['model' => $product]);
        return $request;
    }

    private function context(): ContextInterface
    {
        $store = $this->createStub(StoreInterface::class);
        $store->method('getWebsiteId')->willReturn(3);
        $extension = $this->createStub(ContextExtensionInterface::class);
        $extension->method('getStore')->willReturn($store);
        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extension);
        return $context;
    }
}
