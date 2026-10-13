# GraphCommerce_GraphQlCatalog

Catalog GraphQL fields for any Magento or Mage-OS store. One composer package, one git
repository, one Magento module per directory under `src/`, named as the module. Each module
depends only on the core modules it plugs into.

- `GraphQlCatalog`: the schema of `custom_attributeV2(attribute_code)` (the entry of the
  unfiltered `custom_attributesV2` list), the `attribute` of an attribute value and of an
  aggregation (core's attribute data providers; a category aggregation answers null), and
  `Model/OptionSwatch::KEY`.
- `GraphQlCatalogSwatches`: the `swatch` field on aggregation, attribute and selected options.
  `Model/Resolver/SwatchData` reads the swatch under the key and builds the thumbnail URL of a
  stored image swatch; `Plugin/Output/*` and `Plugin/Layer/AggregationSwatches` fill the key from
  core's swatch helper, and keep an option that carries it.

## Rules

- The modules answer from core alone. A read model (GraphCommerce Catalog Storefront) plugs into
  these resolvers and fills the swatch key; nothing here names it.
- A field keeps the name and the arguments of GraphCommerce's mesh extension.

## Writing rules

- Text a person reads (documentation, comments, commit messages, pull request descriptions,
  text in code) is written in ASD-STE100 Simplified Technical English, for a senior developer.
  No em-dash: use a colon or omit it.
- Comments describe the final state of the code, in the present tense. No history, no TODO.
  A comment is written only where the code is not clear on its own.
- A commit message states the higher goal the commit reaches, in at most a paragraph. It does
  not restate the diff and does not use conventional commit prefixes.
- No function or method that has one call site. Types are derived instead of written by hand.
