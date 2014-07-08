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
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('content/{contentTypeKey}', 'controller.async:getContent')->bind('async.content');
        $controllers->get('migrate/', 'controller.async:getMigrate');

        return $controllers;
    }

    public function getContent(Request $request, Application $app, $contentTypeKey)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $defaultFields = $contentType->getDefaultFields();
        $defaultSort = $defaultFields->forPurpose('datechanged')->getKey();
        $sort = $request->get('sort', $defaultSort);
        $order = $request->get('order', 'asc');
        $offset = $request->get('offset', null);
        $limit = $request->get('limit', null);
        $search = $request->get('search', null);

        $repository = $this->getReadRepository($contentType);

        $getAll = $request->get('originatorContentTypeKey') == $app['config']->get('app/project/contenttype');
        $wheres = $this->getWheres($contentType, $getAll);
        $contents = $repository->get($wheres, false, $sort, $order, $offset, $limit, $search);

        return $this->json(array_values($contents->toArray()));
    }

}
