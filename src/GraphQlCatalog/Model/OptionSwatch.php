<?php
declare(strict_types=1);

namespace GraphCommerce\GraphQlCatalog\Model;

/**
 * The key under which an attribute option, a selected option or an aggregation option carries its
 * swatch for the swatch field of GraphCommerce_GraphQlCatalogSwatches: core's swatch data shape, or a
 * stored swatch (type and value) without its thumbnail URL.
 */
final class OptionSwatch
{
    public const KEY = '_gc_swatch';
}
