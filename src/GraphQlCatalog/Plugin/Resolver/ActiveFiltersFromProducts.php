<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalog\Plugin\Resolver;

use GraphCommerce\GraphQlCatalog\Model\ActiveFilters;
use Magento\CatalogGraphQl\Model\Resolver\Products;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ActiveFiltersFromProducts
{
    public function __construct(
        private readonly ActiveFilters $activeFilters,
    ) {
    }

    public function beforeResolve(Products $subject, Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): void
    {
        $this->activeFilters->set((string)($info->path[0] ?? ''), (array)($args['filter'] ?? []));
    }
}
