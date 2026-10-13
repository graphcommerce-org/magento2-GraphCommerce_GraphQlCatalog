# GraphCommerce_GraphQlCatalog

Fields for the catalog GraphQL schema of any Magento or Mage-OS store. It depends on
Magento_CatalogGraphQl and Magento_EavGraphQl only, so it runs without the catalog read model.

| Field | Answers |
| --- | --- |
| `ProductInterface.custom_attributeV2(attribute_code)` | The entry of `custom_attributesV2` with that code, or null |
| `AttributeValueInterface.attribute`, `Aggregation.attribute` | The attribute metadata, as `customAttributeMetadataV2` returns it; null for a category aggregation |

The fields have the names and arguments of GraphCommerce's mesh extension
(`packages/magento-graphql/schema`), so a storefront drops its mesh resolvers and their
`customAttributeMetadataV2` call per attribute, and reads the frontend input of a filter from
the aggregation instead of an `attributesList` query.

- [`Model/Resolver/ProductCustomAttribute`](Model/Resolver/ProductCustomAttribute.php) takes the
  entry from the `custom_attributesV2` resolver, so a module that serves that list serves this
  field too.
- [`Model/Resolver/Attribute`](Model/Resolver/Attribute.php) answers from core's attribute data
  providers. GraphCommerce_CatalogStorefrontGraphQl serves it from the attribute documents.
- [`Model/OptionSwatch`](Model/OptionSwatch.php) is the key under which an option carries its
  swatch for GraphCommerce_GraphQlCatalogSwatches.
