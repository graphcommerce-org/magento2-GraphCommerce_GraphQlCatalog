<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogSwatches\Plugin\Layer;

use GraphCommerce\GraphQlCatalog\Model\OptionSwatch;
use GraphCommerce\GraphQlCatalogSwatches\Model\Swatches;
use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Eav\Model\Config as EavConfig;

/**
 * Puts the swatch of every option of a select attribute's aggregation on the option, from core's
 * swatch helper. Category, price and boolean values are no option ids and carry no swatch. An
 * aggregation whose options carry the key already keeps them: a plugin with a lower sort order
 * filled them first.
 */
class AggregationSwatches
{
    private const SELECT_INPUTS = ['select', 'multiselect'];

    public function __construct(
        private readonly EavConfig $eavConfig,
        private readonly Swatches $swatches,
    ) {
    }

    public function afterBuild(LayerBuilder $subject, array $layers): array
    {
        foreach ($layers as $key => $layer) {
            $options = $layer['options'] ?? [];
            if (!$options || count(array_filter($options, static fn(array $option) => array_key_exists(OptionSwatch::KEY, $option))) === count($options)) {
                continue;
            }
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $layer['attribute_code']);
            $swatches = $attribute->getAttributeId() && in_array($attribute->getFrontendInput(), self::SELECT_INPUTS, true)
                ? $this->swatches->byOptionIds($attribute, array_column($options, 'value'))
                : [];
            foreach ($options as $index => $option) {
                $layers[$key]['options'][$index][OptionSwatch::KEY] = $swatches[(string)$option['value']] ?? null;
            }
        }

        return $layers;
    }
}
