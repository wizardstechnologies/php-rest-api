<?php

namespace WizardsTest\Exception;

use PHPUnit\Framework\TestCase;
use WizardsRest\ObjectManager\ArrayObjectManager;
use WizardsRest\CollectionManager;
use WizardsRest\Provider;
use WizardsRest\Serializer;
use WizardsRest\ObjectReader\ArrayReader;
use WizardsRest\Paginator\ArrayPagerfantaPaginator;
use Symfony\Component\Routing\RouterInterface;
use Nyholm\Psr7\ServerRequest;
use WizardsRest\Transformer\ArrayTransformer;


class ArrayPagerFantaTest extends TestCase
{
    public function testArraySerializing()
    {
        $source = [
            ['id' => 1, 'name' => 'Book 1', 'author' => 'Author 1', 'editor' => 'Editor 1'],
            ['id' => 2, 'name' => 'Book 2', 'author' => 'Author 2', 'editor' => 'Editor 2'],
        ];
        $router = $this->createMock(RouterInterface::class);
        $request = new ServerRequest('GET', '/books');
        $objectManager = new ArrayObjectManager();
        $paginator = new ArrayPagerfantaPaginator($router);
        $collectionManager = new CollectionManager($paginator, $objectManager);
        $collection = $collectionManager->getPaginatedCollection($source, $request);

        // Transform
        $fractalManager = new \League\Fractal\Manager();
        $reader = new ArrayReader();
        $provider = new Provider(new ArrayTransformer(), $fractalManager, $reader);
        $resource = $provider->transform($collection, $request, null, 'books');

        // Serialize
        $serializer = new Serializer($fractalManager, 'https://mysite.com');
        $this->assertEquals(
            '{"data":[{"id":1,"name":"Book 1","author":"Author 1","editor":"Editor 1"},{"id":2,"name":"Book 2","author":"Author 2","editor":"Editor 2"}]}',
            $serializer->serialize($resource, Serializer::SPEC_DATA_ARRAY, Serializer::FORMAT_JSON)
        );
    }
}
