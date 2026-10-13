# GraphCommerce_GraphQlCatalogInventory

`ProductInterface.stock_sources`: the source items of the product in the enabled sources of the
stock of the website, in source priority order, with the source name, the physical quantity, the
source item status, the in-store pickup flag and the location. Reservations count on the stock
only, so `quantity` is the physical quantity, not the salable quantity. A product without source
items, such as a configurable product, answers an empty list.

[`Model/Resolver/StockSources`](Model/Resolver/StockSources.php) is a batch resolver: one source
item search for every product of the request.
