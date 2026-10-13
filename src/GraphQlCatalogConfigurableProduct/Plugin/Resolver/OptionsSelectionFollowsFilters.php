<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogConfigurableProduct\Plugin\Resolver;

use GraphCommerce\GraphQlCatalog\Model\ActiveFilters;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\ConfigurableProductGraphQl\Model\Resolver\OptionsSelectionMetadata;
use Magento\ConfigurableProductGraphQl\Model\Options\DataProvider\Variant;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * With followFilters, hands core's selection resolver the uids of the values the filter of the
 * products query selects, over the salable variants core selects from.
 */
class OptionsSelectionFollowsFilters
{
    public function __construct(
        private readonly ActiveFilters $activeFilters,
        private readonly Data $helper,
        private readonly Variant $variant,
        private readonly SelectionUidFormatter $selectionUidFormatter,
    ) {
    }

    public function beforeResolve(OptionsSelectionMetadata $subject, Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): ?array
    {
        if (empty($args['followFilters']) || !isset($value['model'])) {
            return null;
        }
        $product = $value['model'];
        $attributeIds = [];
        foreach ($this->helper->getAllowAttributes($product) as $attribute) {
            $attributeIds[$attribute->getProductAttribute()->getAttributeCode()] = (int)$attribute->getAttributeId();
        }
        $variants = $this->helper->getOptions($product, $this->variant->getSalableVariantsByParent($product))['index'] ?? [];
        $selection = $this->selectionUidFormatter->extract((array)($args['configurableOptionValueUids'] ?? []))
            + $this->activeFilters->selection($info, $attributeIds, $variants);
        $args['configurableOptionValueUids'] = array_map(
            fn(int $attributeId) => $this->selectionUidFormatter->encode($attributeId, $selection[$attributeId]),
            array_keys($selection)
        );

        return [$field, $context, $info, $value, $args];
    }
}
