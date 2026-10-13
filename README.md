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

The modules answer from core's resolvers and data providers and need nothing else.
[GraphCommerce Catalog Storefront](https://github.com/graphcommerce-org/magento2-GraphCommerce_CatalogStorefront)
requires this package and serves the same fields from its documents.

## Installation

```sh
composer require graphcommerce/magento-graphql-catalog
bin/magento module:enable GraphCommerce_GraphQlCatalog GraphCommerce_GraphQlCatalogSwatches
bin/magento setup:upgrade
```

`GraphCommerce_GraphQlCatalogSwatches` needs Magento_SwatchesGraphQl. A store without swatches
leaves it disabled.

## Modules

| Module | Adds |
| --- | --- |
| [`GraphQlCatalog`](src/GraphQlCatalog) | `custom_attributeV2`, the `attribute` of a value and an aggregation, the option swatch key |
| [`GraphQlCatalogSwatches`](src/GraphQlCatalogSwatches) | The `swatch` fields from core's swatch helper |

## Extension point

An option array carries its swatch under
[`OptionSwatch::KEY`](src/GraphQlCatalog/Model/OptionSwatch.php): core's swatch data shape, or a
stored swatch (type and value) whose thumbnail URL the swatch field builds. A module that
builds option arrays itself puts the swatch there, and the core plugins keep it. An aggregation
plugin that fills the key takes a sort order below 20, so it runs before the core plugin.

## Development

`phpunit.xml.dist` uses the Magento installation named by `MAGENTO_ROOT`:

```sh
MAGENTO_ROOT=/path/to/magento php vendor/bin/phpunit -c phpunit.xml.dist
```
