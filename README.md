# Mage2 Module Shoaib CatalogSorting

    shoaib/catalog-sorting

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)


## Main Functionalities
The Shoaib CatalogSorting module introduces new sorting features for the Magento 2 catalog, including:

- Best Seller 
- Newest Arrivals 
- Price: Low to High 
- Price: High to Low

`A key feature of this extension is the creation of an index table specifically for best sellers, which enhances sorting performance. Additionally, it modifies the Elasticsearch parameters to incorporate these new sorting options.`

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Shoaib`
 - Enable the module by running `php bin/magento module:enable Shoaib_CatalogSorting`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Run indexer `php bin/magento indexer:reindex`
 - Flush the cache by running `php bin/magento cache:flush`
