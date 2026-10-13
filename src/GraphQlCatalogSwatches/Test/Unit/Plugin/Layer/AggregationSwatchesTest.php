<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogSwatches\Test\Unit\Plugin\Layer;

use GraphCommerce\GraphQlCatalog\Model\OptionSwatch;
use GraphCommerce\GraphQlCatalogSwatches\Model\Swatches;
use GraphCommerce\GraphQlCatalogSwatches\Plugin\Layer\AggregationSwatches;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\Eav\Model\Config as EavConfig;
use PHPUnit\Framework\TestCase;

class AggregationSwatchesTest extends TestCase
{
    private const LAYERS = [
        'color_bucket' => ['attribute_code' => 'color', 'options' => [['label' => 'Red', 'value' => '5', 'count' => 2], ['label' => 'Plain', 'value' => '6', 'count' => 1]]],
        'category_bucket' => ['attribute_code' => 'category_uid', 'options' => [['label' => 'Wands', 'value' => '5', 'count' => 3]]],
        'new_bucket' => ['attribute_code' => 'new', 'options' => [['label' => 'Yes', 'value' => '1', 'count' => 3]]],
    ];

    private function plugin(Swatches $swatches, ?EavConfig $eavConfig = null): AggregationSwatches
    {
        if ($eavConfig === null) {
            $eavConfig = $this->createStub(EavConfig::class);
            $eavConfig->method('getAttribute')->willReturnCallback(function (string $entity, string $code) {
                $attribute = $this->createStub(Attribute::class);
                $attribute->method('getAttributeId')->willReturn($code === 'category_uid' ? null : 1);
                $attribute->method('getFrontendInput')->willReturn($code === 'color' ? 'select' : 'boolean');
                return $attribute;
            });
        }

        return new AggregationSwatches($eavConfig, $swatches);
    }

    public function testTheSelectAttributesTakeTheirSwatchesFromCoresSwatchHelper(): void
    {
        $swatches = $this->createMock(Swatches::class);
        $swatches->expects(self::once())->method('byOptionIds')->with(self::anything(), ['5', '6'])
            ->willReturn(['5' => ['value' => '#ff0000', 'type' => 1], '6' => null]);

        $layers = $this->plugin($swatches)->afterBuild($this->createStub(LayerBuilder::class), self::LAYERS);

        self::assertSame(['value' => '#ff0000', 'type' => 1], $layers['color_bucket']['options'][0][OptionSwatch::KEY]);
        self::assertNull($layers['color_bucket']['options'][1][OptionSwatch::KEY]);
        self::assertNull($layers['category_bucket']['options'][0][OptionSwatch::KEY], 'a category id is no option id');
        self::assertNull($layers['new_bucket']['options'][0][OptionSwatch::KEY], 'a boolean value is no option id');
    }

    public function testOptionsThatCarryTheKeyAreKeptWithoutALookup(): void
    {
        $layers = self::LAYERS;
        foreach ($layers as $key => $layer) {
            foreach ($layer['options'] as $index => $option) {
                $layers[$key]['options'][$index][OptionSwatch::KEY] = ['type' => 0, 'value' => 'document'];
            }
        }
        $eavConfig = $this->createMock(EavConfig::class);
        $eavConfig->expects(self::never())->method('getAttribute');
        $swatches = $this->createMock(Swatches::class);
        $swatches->expects(self::never())->method('byOptionIds');

        self::assertSame($layers, $this->plugin($swatches, $eavConfig)->afterBuild($this->createStub(LayerBuilder::class), $layers));
    }
}
