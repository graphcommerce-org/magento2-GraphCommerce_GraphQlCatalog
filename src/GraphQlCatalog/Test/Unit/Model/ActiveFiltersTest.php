<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalog\Test\Unit\Model;

use GraphCommerce\GraphQlCatalog\Model\ActiveFilters;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\TestCase;

class ActiveFiltersTest extends TestCase
{
    private const ATTRIBUTES = ['color' => 93, 'size' => '144'];
    private const VARIANTS = [
        11 => [93 => '50', 144 => '166'],
        12 => [93 => '50', 144 => '167'],
        13 => [93 => '57', 144 => '166'],
    ];

    public function testAnAttributeFilteredOnOneValueIsSelected(): void
    {
        $filters = new ActiveFilters();
        $filters->set('listing', ['color' => ['eq' => '50'], 'size' => ['in' => ['166', '167']], 'category_id' => ['eq' => '3']]);

        self::assertSame([93 => 50], $filters->selection($this->info('listing'), self::ATTRIBUTES, self::VARIANTS));
        self::assertSame([], $filters->selection($this->info('products'), self::ATTRIBUTES, self::VARIANTS));
    }

    public function testASelectionNoSalableVariantCarriesSelectsNothing(): void
    {
        $filters = new ActiveFilters();
        $filters->set('products', ['color' => ['eq' => '57'], 'size' => ['in' => ['167']]]);
        self::assertSame([], $filters->selection($this->info('products'), self::ATTRIBUTES, self::VARIANTS));

        $filters->set('products', ['color' => ['eq' => '57'], 'size' => ['in' => ['166']]]);
        self::assertSame([93 => 57, 144 => 166], $filters->selection($this->info('products'), self::ATTRIBUTES, self::VARIANTS));
    }

    public function testTheRequestStateResets(): void
    {
        $filters = new ActiveFilters();
        $filters->set('products', ['color' => ['eq' => '50']]);
        $filters->_resetState();

        self::assertSame([], $filters->selection($this->info('products'), self::ATTRIBUTES, self::VARIANTS));
    }

    private function info(string $responseKey): ResolveInfo
    {
        $info = $this->createStub(ResolveInfo::class);
        $info->path = [$responseKey, 'items', 0, 'configurable_product_options_selection'];

        return $info;
    }
}
