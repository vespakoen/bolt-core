<?php

namespace Bolt\Core\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Illuminate\Support\Collection;

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

        $controllers->get('migrate/', 'controller.async:getMigrate');

        return $controllers;
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

}
