## Doc flow
- explain the purpose and portability
- most common use case, symfony + json api in 10 minutes
- links to advanced docs: symfony guide, laravel guide, Plain Old Php
- clear split between packages: symfony bundle, laravel package, library

## Use without config

```
<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Service\Fraktal;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArtistController
{
	private $doctrineReader;
	
    /**
     * @Route("/artists/{id}")
     */
    public function getArtist(Artist $artist, Request $request)
    {
    	$reader = new DoctrineAnnotationReader($this->doctrineReader);
    	$entityTransformer = new EntityTransformer($reader); // or any other ObjectReaderInterface 
    	$fraktal = new Fraktal($entityTransformer); // or any TransformerAbstract
        $resource = $this->fraktal->transform($artist, $request);

        return new Response(
            $this->fraktal->serialize(
                $resource,
                Fraktal::SPEC_JSONAPI,
                Fraktal::FORMAT_JSON
            ),
            200,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }
    
    public function getArtists(Request $request)
    {
        $artists = $this->fraktal->getPaginatedCollection(Artist::class, $request);
        $paginatorAdapter = $this->fraktal->getPaginationAdapter($request);
        $resource = $this->fraktal->transform($artists, $request);
        $resource->setPaginator($paginatorAdapter);

        return new Response(
            $this->fraktal->serialize(
                $resource,
                Fraktal::SPEC_JSONAPI,
                Fraktal::FORMAT_JSON
            ),
            200,
            ['Content-Type' => 'application/vnd.api+json']
        );
    }    
}
```

## Todo
- !tests
- ~sparse fieldset
- ~advanced filter operators such as like,in,between,
- ~eager fetch helper interface + orm
- ?serialization groups