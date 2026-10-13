<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalogSwatches\Test\Unit\Model\Resolver;

use GraphCommerce\GraphQlCatalog\Model\OptionSwatch;
use GraphCommerce\GraphQlCatalogSwatches\Model\Resolver\SwatchData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Swatches\Helper\Media as SwatchMedia;
use PHPUnit\Framework\TestCase;

class SwatchDataTest extends TestCase
{
    private function resolve(array $value): ?array
    {
        $media = $this->createStub(SwatchMedia::class);
        $media->method('getSwatchMediaUrl')->willReturn('https://shop/media/attribute/swatch');
        $media->method('getFolderNameSize')->willReturn('110x90');

        return (new SwatchData($media))->resolve($this->createStub(Field::class), null, $this->createStub(ResolveInfo::class), $value);
    }

    public function testADocumentImageSwatchGetsItsThumbnailUrl(): void
    {
        self::assertSame(
            ['type' => 2, 'value' => '/p/a/pattern.png', 'thumbnail' => 'https://shop/media/attribute/swatch/swatch_thumb/110x90/p/a/pattern.png'],
            $this->resolve([OptionSwatch::KEY => ['type' => 2, 'value' => '/p/a/pattern.png']])
        );
    }

    public function testACoreSwatchKeepsItsThumbnailAndAColourHasNone(): void
    {
        $core = ['value' => '/p/a/pattern.png', 'type' => 2, 'thumbnail' => 'https://cdn/thumb.png'];
        self::assertSame($core, $this->resolve([OptionSwatch::KEY => $core]));
        self::assertSame(['type' => 1, 'value' => '#ff0000'], $this->resolve([OptionSwatch::KEY => ['type' => 1, 'value' => '#ff0000']]));
    }

    public function testNoSwatchAndAnEmptySwatchTypeAnswerNull(): void
    {
        self::assertNull($this->resolve(['value' => '5']));
        self::assertNull($this->resolve([OptionSwatch::KEY => null]));
        self::assertNull($this->resolve([OptionSwatch::KEY => ['type' => 3, 'value' => '']]));
    }
}
