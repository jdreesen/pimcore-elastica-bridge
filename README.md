# Elastica Bridge for Pimcore

[![Latest Version on Packagist](https://img.shields.io/packagist/v/valantic/pimcore-elastica-bridge.svg?style=flat-square)](https://packagist.org/packages/valantic/pimcore-elastica-bridge)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

**NO support is provided!**

This package is developed by [valantic CEC Schweiz](https://www.valantic.com/en/services/digital-business/) and is under active development.

## Setup

```
composer require valantic/pimcore-elastica-bridge
```

### Configuration: `app/config/config.yml`

```yaml
valantic_elastica_bridge:
  client:
    host: 'localhost'
    port: 9200
```

### Define Document for Pimcore DataObject `src/AppBundle/Elasticsearch/Document/ProductDocument.php`

```php
<?php

namespace AppBundle\Elasticsearch\Document;

use Pimcore\Model\DataObject\Product;
use Valantic\ElasticaBridgeBundle\DocumentType\AbstractDocument;
use Valantic\ElasticaBridgeBundle\DocumentType\DocumentInterface;

class ProductDocument extends AbstractDocument
{
    public function getType(): string
    {
        return DocumentInterface::TYPE_OBJECT;
    }

    public function getSubType(): string
    {
        return Product::class;
    }
}
```

### Define Elasticsearch Index `src/AppBundle/Elasticsearch/Index/Product/ProductIndex.php`

```php
<?php

namespace AppBundle\Elasticsearch\Index\Product;

use AppBundle\Elasticsearch\Index\Product\Document\ProductIndexDocument;
use Elastica\Query\Match;
use Pimcore\Model\DataObject\Category;
use Valantic\ElasticaBridgeBundle\Index\AbstractIndex;

class ProductIndex extends AbstractIndex
{
    public function getName(): string
    {
        return 'product';
    }

    public function getMapping(): array
    {
        return [];
    }

    public function getAllowedDocuments(): array
    {
        return [ProductIndexDocument::class];
    }

    public function filterByCategory(Category $category): Match
    {
        return new Match('categories', sprintf(',%s,', $category->getId()));
    }
}
```

### Define Document in Index `src/AppBundle/Elasticsearch/Index/Product/Document/ProductIndexDocument.php`

```php
<?php

namespace AppBundle\Elasticsearch\Index\Product\Document;

use AppBundle\Elasticsearch\Document\ProductDocument;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\Element\AbstractElement;
use Valantic\ElasticaBridgeBundle\DocumentType\Index\AttributesTrait;
use Valantic\ElasticaBridgeBundle\DocumentType\Index\IndexDocumentInterface;
use Valantic\ElasticaBridgeBundle\DocumentType\Index\ListingTrait;

class ProductIndexDocument extends ProductDocument implements IndexDocumentInterface
{
    use ListingTrait;
    use AttributesTrait;

    public function getNormalized(AbstractElement $element): array
    {
        /**@var Product $element */
        return array_merge(
            $this->plainAttributes($element, ['ean']),
            $this->localizedAttributes($element, ['name']),
            $this->relationAttributes($element, ['categories']),
            $this->children($element, ['variants']),
            $this->childrenRecursive($element, ['allVariants']),
        );
    }

    public function shouldIndex(AbstractElement $element): bool
    {
        /**@var Product $element */
        return $element->isPublished();
    }
}
```

## Usage

```php
<?php

namespace AppBundle\Controller\ProductManagement;

use AppBundle\Elasticsearch\Index\Product\ProductIndex;
use Pimcore\Templating\Model\ViewModel;

class CategoryController
{
    public function productListAction(ProductIndex $productIndex): ViewModel
    {
        $products = [];
        foreach ($productIndex->getElasticaIndex()->search($productIndex->filterByCategory($category))->getDocuments() as $esDoc) {
            $products[] = $productIndex->getIndexDocumentInstance($esDoc)->getPimcoreElement($esDoc);
        }
        
        $this->products = $products;
    
        return $this->view;
    }
}
```
