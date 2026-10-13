<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogConfigurableProduct\Test\Unit\Plugin\Resolver;

use GraphCommerce\GraphQlCatalog\Model\ActiveFilters;
use GraphCommerce\GraphQlCatalogConfigurableProduct\Plugin\Resolver\OptionsSelectionFollowsFilters;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProductGraphQl\Model\Options\SelectionUidFormatter;
use Magento\ConfigurableProductGraphQl\Model\Resolver\OptionsSelectionMetadata;
use Magento\ConfigurableProductGraphQl\Model\Options\DataProvider\Variant;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\TestCase;

class OptionsSelectionFollowsFiltersTest extends TestCase
{
    public function testTheFilterSelectsUnderTheExplicitUids(): void
    {
        $formatter = new SelectionUidFormatter(new Uid());
        $filters = new ActiveFilters();
        $filters->set('products', ['color' => ['eq' => '50'], 'size' => ['eq' => '166']]);
        $info = $this->createStub(ResolveInfo::class);
        $info->path = ['products', 'items', 0, 'configurable_product_options_selection'];
        $product = $this->createStub(Product::class);

        $helper = $this->createStub(Data::class);
        $helper->method('getAllowAttributes')->willReturn([$this->attribute(93, 'color'), $this->attribute(144, 'size')]);
        $helper->method('getOptions')->willReturn(['index' => [11 => [93 => '50', 144 => '166'], 12 => [93 => '50', 144 => '167']]]);
        $plugin = new OptionsSelectionFollowsFilters($filters, $helper, $this->createStub(Variant::class), $formatter);
        $subject = $this->createStub(OptionsSelectionMetadata::class);
        $field = $this->createStub(Field::class);

        self::assertNull($plugin->beforeResolve($subject, $field, null, $info, ['model' => $product], []));
        $args = $plugin->beforeResolve($subject, $field, null, $info, ['model' => $product], [
            'followFilters' => true,
            'configurableOptionValueUids' => [$formatter->encode(144, 167)],
        ])[4];
        self::assertSame([144 => 167, 93 => 50], $formatter->extract($args['configurableOptionValueUids']));
    }

    private function attribute(int $id, string $code): Attribute
    {
        $productAttribute = $this->createStub(AbstractAttribute::class);
        $productAttribute->method('getAttributeCode')->willReturn($code);
        $attribute = (new \ReflectionClass(Attribute::class))->newInstanceWithoutConstructor();
        $attribute->setData(['attribute_id' => $id, 'product_attribute' => $productAttribute]);

        return $attribute;
    }
}
