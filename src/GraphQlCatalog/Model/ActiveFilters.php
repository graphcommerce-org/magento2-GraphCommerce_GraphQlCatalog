<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalog\Model;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * The filter argument of every products field of the request, by its response key.
 */
class ActiveFilters implements ResetAfterRequestInterface
{
    /** @var array<string, array> */
    private array $filters = [];

    public function set(string $responseKey, array $filter): void
    {
        $this->filters[$responseKey] = $filter;
    }

    /**
     * The configurable option selection the filter of the products field above `$info` names:
     * every super attribute the filter limits to one value. Empty when no salable variant
     * carries all of these values.
     *
     * @param array<string, int|string> $attributeIds the super attribute ids by attribute code
     * @param array<array<int|string, int|string|null>> $variants the value per super attribute id of each salable variant
     * @return array<int, int> the value index by super attribute id
     */
    public function selection(ResolveInfo $info, array $attributeIds, array $variants): array
    {
        $selection = [];
        foreach ($this->filters[$info->path[0] ?? ''] ?? [] as $code => $condition) {
            $values = isset($condition['eq']) ? [$condition['eq']] : (array)($condition['in'] ?? []);
            if (isset($attributeIds[$code]) && count($values) === 1) {
                $selection[(int)$attributeIds[$code]] = (int)reset($values);
            }
        }
        foreach ($variants as $values) {
            $values = array_map('intval', array_filter($values, 'is_numeric'));
            if (array_intersect_assoc($selection, $values) === $selection) {
                return $selection;
            }
        }

        return [];
    }

    public function _resetState(): void
    {
        $this->filters = [];
    }
}
