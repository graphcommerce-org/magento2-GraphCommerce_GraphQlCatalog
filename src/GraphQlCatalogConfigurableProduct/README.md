# GraphCommerce_GraphQlCatalogConfigurableProduct

`followFilters: Boolean` on `ConfigurableProduct.configurable_product_options_selection`. With
`true`, the selection takes the values the filter of the products query names: every super
attribute the filter limits to one value (`eq`, or `in` with one value). An attribute filtered on
several values stays open. When no salable variant carries all of the selected values, the filter
selects nothing. A uid in `configurableOptionValueUids` takes precedence for its attribute.

```graphql
products(filter: { color: { eq: "50" } }) {
  items {
    ... on ConfigurableProduct {
      configurable_product_options_selection(followFilters: true) {
        media_gallery { url }
        variant { sku }
      }
    }
  }
}
```

[`Plugin/Resolver/OptionsSelectionFollowsFilters`](Plugin/Resolver/OptionsSelectionFollowsFilters.php)
(sort order 20) reads the salable variants as core's resolver reads them and hands it the uids.
