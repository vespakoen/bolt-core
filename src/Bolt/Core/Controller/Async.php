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
        $namespace = str_replace('.', '', $app['project.service']->getCurrentProject()->get('namespace'));

        $json = `curl http://search.trapps.nl/$namespace/_search\?size\=1000 -XPOST -d '{"fields": ["image_lowres", "image_list_highres", "title_nl"], "query": {"filtered": {"query": {"match_all": {}},"filter": {"exists" : { "field" : "image_lowres" }}}}}'`;
        $data = json_decode($json, true);

        $images = array();
        foreach ($data['hits']['hits'] as $item) {
            $thumb = $item['fields']['image_list_highres'][0];
            $image = $item['fields']['image_lowres'][0];
            $title = $item['fields']['title_nl'][0];

            $contentType = $app['contenttypes']->findBy('es_type', $item['_type']);
            $folder = $contentType ? ucfirst($contentType->get('name')) : 'Geen categorie';
            $images[] = array(
                "thumb" => $thumb,
                "image" => $image,
                "title" => $title,
                "folder" => $folder
            );
        }

        return json_encode($images);
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
