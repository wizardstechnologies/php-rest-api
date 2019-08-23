# Wizards Technologies' PHP REST API Library
A framework agnostic PHP library based on [fractal](https://fractal.thephpleague.com/) to help you craft beautiful REST APIs.

## Goals
The main goal is to provide an easy to use and powerful REST API library that seamlessly integrates with modern frameworks.

The paradigm reads as follow:
- A method will ask for a given resource (usually a controller), sending over an [http request](https://www.php-fig.org/psr/psr-7/) that we _Parse_ to find standardized parameters. 
  A resource is a data object, that you can fetch from a data store - as a collection or as an entry. Thoses resources can have relationships, can be filtered, can be modified, ....
- The library will fetch the resource(s) according to the given _Object Manager_ (something like an orm, odm) and request 
- It then transforms the data into standardized resource(s) given the provided _Object Reader_ (there are many ways to configure resources, such as doctrine annotations, configuration files, ...)
- It sends the found resources to fractal with the appropriate _Transformer_ for serialization on the given output format.

## Installation

```
composer require wizards/rest-api
```

## Usage

The library's conventions are based on the [jsonapi ones](http://jsonapi.org/format/). 

### Query Paramters

The _RestQueryParser_ will expect those query parameters:

- Collection
	- `sort`: `name` to sort by ascending name, `-name` to sort by descending name. Example: `?sort=-date`
	- `filter` to filter resources by values. Example: `?filter[name]=dupont&filter[surname]=thomas`
	- `filteroperator` to change the default filter opetator from `=` to something else. available operators: `<`, `>`, `<=`, `>=`, `!=`. Example: `?filter[age]=18&filteroperator[age]=>=`
	- `include` to include relationships data. Example: `/books?include=author,editor`
	- `limit`: how many results you want to see by page.
	- `page`: the page number. Starts at 1.
- Single resource
	- `include` to include relationships data. Example: `/books/1?include=author,editor`

## Examples

### Plain ol' PHP

```php
<?php

namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface;
use WizardsRest\ObjectManager\ArrayObjectManager;
use WizardsRest\CollectionManager;
use WizardsRest\Provider;
use WizardsRest\Serializer;
use WizardsRest\ObjectReader\ArrayReader;
use WizardsRest\Paginator\ArrayPagerfantaPaginator;
use Symfony\Component\Routing\RouterInterface;
use WizardsRest\Transformer\ArrayTransformer;

Class BookController {
    private $source = [
        ['name' => 'Book 1', 'author' => 'Author 1', 'editor' => 'Editor 1'],
        ['name' => 'Book 2', 'author' => 'Author 2', 'editor' => 'Editor 2'],
	];
    
	// Books controller. Somehow, this is called
    public function getBooks(RouterInterface $router, ServerRequestInterface $request) {
        // Fetch
        $objectManager = new ArrayObjectManager();
        $paginator = new ArrayPagerfantaPaginator($router);
        $collectionManager = new CollectionManager($paginator, $objectManager);
        $collection = $collectionManager->getPaginatedCollection($this->source, $request);
        
        // Transform
        $fractalManager = new \League\Fractal\Manager();
        $reader = new ArrayReader();
        $provider = new Provider(new ArrayTransformer(), $fractalManager, $reader);
        $resource = $provider->transform($collection, $request, null, 'books');
        
        // Serialize
        $serializer = new Serializer($fractalManager, 'https://mysite.com');
        return $serializer->serialize($resource, Serializer::SPEC_DATA_ARRAY, Serializer::FORMAT_ARRAY);
    }
}

```
### Symfony

See the documentation on [Wizards Technologies' REST API Bundle](https://github.com/wizardstechnologies/rest-api-bundle)

### Laravel

We are actively looking for laravel developers to support it !

## Future plans

- Add more tests
- Add the sparse fieldset feature
- Add advanced filter operators such as like,in or between
- Optimize how the data are fetched from the source.
- Think about serialization groups.