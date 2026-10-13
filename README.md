# GraphCommerce GraphQL Catalog

Fields for the catalog GraphQL schema of a Magento or Mage-OS store, with the names and
arguments of GraphCommerce's mesh extension (`packages/magento-graphql/schema`). A storefront
drops its mesh resolvers and the `customAttributeMetadataV2` call they make per attribute, and
reads the frontend input and the swatches of a filter from the listing query.

| Field | Module | Answers |
| --- | --- | --- |
| `ProductInterface.custom_attributeV2(attribute_code)` | `GraphQlCatalog` | The entry of `custom_attributesV2` with that code, or null |
| `AttributeValueInterface.attribute`, `Aggregation.attribute` | `GraphQlCatalog` | The attribute metadata, as `customAttributeMetadataV2` returns it; null for a category aggregation |
| `swatch` on `AggregationOptionInterface`, `CustomAttributeOptionInterface`, `AttributeSelectedOptionInterface` | `GraphQlCatalogSwatches` | The `SwatchDataInterface` of an option of a swatch attribute, or null |
| `ConfigurableProduct.configurable_product_options_selection(followFilters)` | `GraphQlCatalogConfigurableProduct` | The selection the filter of the products query names: every super attribute the filter limits to one value, when a salable variant carries all of them |
| `ProductInterface.stock_sources` | `GraphQlCatalogInventory` | The physical quantity, status, pickup flag and location per enabled source of the website's stock |

The modules answer from core's resolvers and data providers and need nothing else.
[GraphCommerce Catalog Storefront](https://github.com/graphcommerce-org/magento2-GraphCommerce_CatalogStorefront)
requires this package and serves the same fields from its documents.

## Installation

```sh
composer require graphcommerce/magento-graphql-catalog
bin/magento module:enable GraphCommerce_GraphQlCatalog GraphCommerce_GraphQlCatalogSwatches \
    GraphCommerce_GraphQlCatalogConfigurableProduct GraphCommerce_GraphQlCatalogInventory
bin/magento setup:upgrade
```

`GraphCommerce_GraphQlCatalogSwatches` needs Magento_SwatchesGraphQl and
`GraphCommerce_GraphQlCatalogConfigurableProduct` needs Magento_ConfigurableProductGraphQl and
`GraphCommerce_GraphQlCatalogInventory` needs MSI. A store without them leaves the module disabled.

## Modules

| Module | Adds |
| --- | --- |
| [`GraphQlCatalog`](src/GraphQlCatalog) | `custom_attributeV2`, the `attribute` of a value and an aggregation, the option swatch key |
| [`GraphQlCatalogSwatches`](src/GraphQlCatalogSwatches) | The `swatch` fields from core's swatch helper |
| [`GraphQlCatalogConfigurableProduct`](src/GraphQlCatalogConfigurableProduct) | `followFilters` on the configurable option selection |
| [`GraphQlCatalogInventory`](src/GraphQlCatalogInventory) | `stock_sources` from MSI's source items |

## Extension point

An option array carries its swatch under
[`OptionSwatch::KEY`](src/GraphQlCatalog/Model/OptionSwatch.php): core's swatch data shape, or a
stored swatch (type and value) whose thumbnail URL the swatch field builds. A module that
builds option arrays itself puts the swatch there, and the core plugins keep it. An aggregation
plugin that fills the key takes a sort order below 20, so it runs before the core plugin.

[`ActiveFilters`](src/GraphQlCatalog/Model/ActiveFilters.php) holds the filter of every products
field of the request and turns it into a configurable option selection over the values of the
salable variants a resolver passes. A selection plugin that serves from another store takes a
sort order below 20, so the core plugin runs only when it hands over to core.

## Development

`phpunit.xml.dist` uses the Magento installation named by `MAGENTO_ROOT`:

```sh
MAGENTO_ROOT=/path/to/magento php vendor/bin/phpunit -c phpunit.xml.dist
```
