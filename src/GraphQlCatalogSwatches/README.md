# GraphCommerce_GraphQlCatalogSwatches

The `swatch` field (`SwatchDataInterface`, as `ConfigurableProductOptionValue.swatch`) on
`AggregationOptionInterface`, `CustomAttributeOptionInterface` and
`AttributeSelectedOptionInterface`, for any Magento or Mage-OS store. It depends on
Magento_SwatchesGraphQl and GraphCommerce_GraphQlCatalog, so it runs without the catalog read
model. Only the options of a swatch attribute carry a swatch; category, price and boolean
aggregation values are no option ids and answer null.

- [`Model/Resolver/SwatchData`](Model/Resolver/SwatchData.php) answers the swatch an option
  carries under `OptionSwatch::KEY`, and builds the thumbnail URL of a stored image swatch.
- `Plugin/Output/*` put the swatch on core's attribute metadata and attribute value providers,
  [`Plugin/Layer/AggregationSwatches`](Plugin/Layer/AggregationSwatches.php) on the aggregation
  options, all from core's swatch helper ([`Model/Swatches`](Model/Swatches.php)). An option that
  carries the key already keeps its swatch: GraphCommerce_CatalogStorefrontSwatchesGraphQl puts
  the document swatch there first, with a lower sort order (10) than the layer plugin (20).
