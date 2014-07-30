<?php

namespace Bolt\Core\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Illuminate\Support\Collection;

use Bolt\Core\Config\Object\Collection\ContentCollection;

use DateTime;

class Async extends Controller implements ControllerProviderInterface
{
    public function __construct($app, $storageService)
    {
        $this->app = $app;
        $this->storageService = $storageService;
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('content/{contentTypeKey}/{id}', 'controller.async:getContent')
            ->bind('async.content')
            ->value('id', null);

        $controllers->get('images', 'controller.async:getImages');

        $controllers->get('partial/{contentTypeKey}/form', 'controller.async:getForm')->bind('form');

        $controllers->get('migrate/', 'controller.async:getMigrate');


        return $controllers;
    }

    public function getImages(Request $request, Application $app)
    {
        return '[
    { "thumb": "json/images/1_m.jpg", "image": "json/images/1.jpg", "title": "Image 1", "folder": "Folder 1" },
    { "thumb": "json/images/2_m.jpg", "image": "json/images/2.jpg", "title": "Image 2", "folder": "Folder 1" },
    { "thumb": "json/images/3_m.jpg", "image": "json/images/3.jpg", "title": "Image 3", "folder": "Folder 1" },
    { "thumb": "json/images/4_m.jpg", "image": "json/images/4.jpg", "title": "Image 4", "folder": "Folder 1" },
    { "thumb": "json/images/5_m.jpg", "image": "json/images/5.jpg", "title": "Image 5", "folder": "Folder 1" },
    { "thumb": "json/images/1_m.jpg", "image": "json/images/1.jpg", "title": "Image 6", "folder": "Folder 1" },
    { "thumb": "json/images/2_m.jpg", "image": "json/images/2.jpg", "title": "Image 7", "folder": "Folder 1" },
    { "thumb": "json/images/3_m.jpg", "image": "json/images/3.jpg", "title": "Image 8", "folder": "Folder 1" },
    { "thumb": "json/images/4_m.jpg", "image": "json/images/4.jpg", "title": "Image 9", "folder": "Folder 1" },
    { "thumb": "json/images/5_m.jpg", "image": "json/images/5.jpg", "title": "Image 10", "folder": "Folder 2" },
    { "thumb": "json/images/1_m.jpg", "image": "json/images/1.jpg", "title": "Image 11", "folder": "Folder 2" },
    { "thumb": "json/images/2_m.jpg", "image": "json/images/2.jpg", "title": "Image 12", "folder": "Folder 2" }
]';
    }

    public function getContent(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        if (is_null($id)) {
            $contents = $this->storageService->getForListing($contentType, $request);
        } else {
            $contents = $this->storageService->getForManage($contentType, $id);
            if ( ! $contents) {
                $app->abort(404, "Contenttype $contentTypeKey with id $id does not exist.");
            }
        }

        return $this->json($contents->toArray());
    }

    public function getForm(Request $request, Application $app, $contentTypeKey)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $content = $this->storageService->getForManage($contentType, null);

        if ($autoRelate = $contentType->get('auto_relate')) {
            foreach ($autoRelate as $relateKey) {
                $relationKey = $app['config']->get('app/' . $relateKey . '/contenttype');
                $related = $content->getAttribute('outgoing.' . $relationKey, new ContentCollection);
                $related->put($app[$relateKey]->getId(), $app[$relateKey]);
                $content->setAttribute('outgoing.' . $relationKey, $related);
            }
        }

        return $contentType->getViewForForm($content);
    }

}
