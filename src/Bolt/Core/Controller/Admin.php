<?php

namespace Bolt\Core\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

use Illuminate\Support\Collection;

use Bolt\Core\Config\Object\Collection\ContentCollection;
use Bolt\Core\Config\Object\Factory\Content;

class Admin extends Controller implements ControllerProviderInterface
{
    public function __construct($app, $storageService)
    {
        $this->app = $app;
        $this->storageService = $storageService;
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('redirect', 'controller.admin:getRedirect');
        $controllers->get('dashboard', 'controller.admin:getDashboard')->bind('admin.dashboard');
        $controllers->get('map', 'controller.admin:getMap')->bind('admin.map');
        $controllers->get('graph', 'controller.admin:getGraph')->bind('admin.graph');
        $controllers->get('contentaction', 'controller.admin:getContentaction')->bind('contentaction');
        $controllers->get('setproject/{projectId}', 'controller.admin:getSetproject')->bind('setproject');
        $controllers->get('{contentTypeKey}/', 'controller.admin:getOverview')->bind('overview');
        $controllers->get('{contentTypeKey}/manage', 'controller.admin:getManage')->bind('manage.new');
        $controllers->get('{contentTypeKey}/manage/{id}', 'controller.admin:getManage')->bind('manage');
        $controllers->get('{contentTypeKey}/manage-single/{id}', 'controller.admin:getManageSingle')->bind('manage.single');
        $controllers->post('{contentTypeKey}/manage', 'controller.admin:postManage');
        $controllers->post('{contentTypeKey}/manage/{id}', 'controller.admin:postManage');
        $controllers->get('{contentTypeKey}/reorder/{id}', 'controller.admin:getReorder')->bind('reorder');
        $controllers->get('{contentTypeKey}/duplicate/{id}/{destinationProject}', 'controller.admin:getDuplicate')->bind('duplicate');
        $controllers->get('{contentTypeKey}/delete/{id}', 'controller.admin:getDeletecontent')->bind('deletecontent');
        $controllers->get('reset-elasticsearch', 'controller.admin:getResetElasticsearch');
        $controllers->get('/', 'controller.admin:getDashboard')->bind('dashboard');

        return $controllers;
    }

    public function getRedirect(Request $request, Application $app)
    {
        if ($app['user']->hasRole('ROLE_COMPANY_OWNER')) {
            return $this->to('manage.single', array(
                'contentTypeKey' => 'companies',
                'id' => $app['user']->getCompany()->getId()
            ));
        } else {
            return $this->to('dashboard');
        }
    }

    public function getDashboard(Request $request, Application $app)
    {
        $contentTypes = array();
        $contentTypeContent = new Collection();
        foreach ($app['contenttypes'] as $contentType) {
            // skip stuff we don't want the user to see
            $role = $contentType->get('role', 'ROLE_USER');
            if ( ! $app['user']->hasRole($role) || $contentType->get('system', false) == true) continue;

            $contents = $this->storageService->getForListing($contentType, $request);
            $contentTypeContent->put($contentType->getKey(), $contents);
            $contentTypes[] = $contentType;
        }

        return $this->view('layouts/dashboard', compact('contentTypeContent', 'contentTypes'));
    }

    public function getMap(Request $request, Application $app)
    {
        return $this->view('layouts/map');
    }

    public function getOverview(Request $request, Application $app, $contentTypeKey)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $contents = $this->storageService->getForListing($contentType, $request);

        $paginator = $contents->getPaginator();

        return $this->view('layouts/overview', compact('contents', 'contentType', 'paginator'));
    }

    public function getManage(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $content = $this->storageService->getForManage($contentType, $id);

        if( ! $content) {
            $app->abort(404);
        }

        if ($autoRelate = $contentType->get('auto_relate')) {
            foreach ($autoRelate as $relateKey) {
                $relationKey = $app['config']->get('app/' . $relateKey . '/contenttype', $relateKey);
                $related = $content->getAttribute('outgoing.' . $relationKey, new ContentCollection);

                $appKey = $app['contenttypes']
                    ->get($relateKey)
                    ->get('app_key');

                $related->put($app[$appKey]->getId(), $app[$appKey]);
                $content->setAttribute('outgoing.' . $relationKey, $related);
            }
        }

        $view = $this->view('layouts/manage', compact('content', 'contentType'));

        $app['session']->getFlashBag()->clear();

        return $view;
    }

    public function getManageSingle(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $content = $this->storageService->getForManage($contentType, $id);

        if( ! $content) {
            $app->abort(404);
        }

        if ($autoRelate = $contentType->get('auto_relate')) {
            foreach ($autoRelate as $relateKey) {
                $relationKey = $app['config']->get('app/' . $relateKey . '/contenttype', $relateKey);
                $related = $content->getAttribute('outgoing.' . $relationKey, new ContentCollection);

                $appKey = $app['contenttypes']
                    ->get($relateKey)
                    ->get('app_key');

                $related->put($app[$appKey]->getId(), $app[$appKey]);
                $content->setAttribute('outgoing.' . $relationKey, $related);
            }
        }

        $view = $this->view('layouts/manage_single', compact('content', 'contentType'));

        $app['session']->getFlashBag()->clear();

        return $view;
    }

    public function postManage(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        $success = true;

        $input = $request->request->all();
        foreach ($input as $contentTypeKey => $items) {
            if(substr($contentTypeKey, 0, 1) == "_") {
                continue;
            }

            if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
               $app->abort(404, "Contenttype $contentTypeKey does not exist.");
            }

            foreach ($items as $itemId => $item) {
                $parameters = new ParameterBag($item);
                if (is_null($id)) {
                    $isSuccessful = $this->storageService->insert($contentType, $parameters);
                } else {
                    $isSuccessful = $this->storageService->update($contentType, $parameters, $itemId);
                }

                if ( ! $isSuccessful) {
                    $success = false;
                }
            }
        }

        if ($success && ! $request->get('redirect') == "back") {
            return $this->to('overview', array(
                'contentTypeKey' => $contentTypeKey
            ));
        } else {
            return $this->back();
        }
    }

    public function getDeletecontent(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $parameters = $request->request;

        $isSuccessful = $this->storageService->delete($contentType, $parameters, $id);

        return $this->to('overview', array(
            'contentTypeKey' => $contentTypeKey
        ));
    }

    public function getReorder(Request $request, Application $app, $contentTypeKey, $id)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }
        $item = $this->storageService->getForManage($contentType, $id);

        $direction = $request->get('direction');

        if ($direction == 'up') {
            $from = $item->get('weight');
            $to = $from - 1;
        } else {
            $from = $item->get('weight');
            $to = $from + 1;
        }

        $this->storageService->reorder($contentType, $request, $id, $from, $to);

        return $this->back();
    }

    public function getDuplicate(Request $request, Application $app, $contentTypeKey, $id, $destinationProject)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $item = $this->storageService->getForManage($contentType, $id);

        $data = $item->toArray();

        foreach ($data['incoming'] as $type => $items) {
            $data['links'][$type] = array_keys($items);
        }

        foreach ($data['outgoing'] as $type => $items) {
            $data['links'][$type] = array_keys($items);
        }

        $data['links']['apps'] = array($destinationProject);

        unset($data['incoming']);
        unset($data['outgoing']);

        $this->storageService->insert($contentType, new ParameterBag($data));

        return $this->back();
    }

    public function getResetElasticsearch(Request $request, Application $app)
    {
        ini_set('max_execution_time', 0);

        $projects = $app['projects'];

        $projectsKey = $app['config']->get('app/project/contenttype');
        $projectContentType = $app['contenttypes']->get($projectsKey);
        $project = $app['content.factory']->create(array(
            'namespace' => 'trapps',
            'unfiltered' => true
        ), $projectContentType);
        $projects->push($project);

        $projects->each(function($project) use ($app, $projects, $projectContentType) {
            $newProject = $app['content.factory']->create(array(
                'namespace' => $project->get('namespace') . '-web',
                'unfiltered' => true
            ), $projectContentType);
            $projects->push($newProject);
        });

        foreach ($projects as $project) {
            $app['elasticsearch.manager']->dropIndex($project);
            $app['elasticsearch.manager']->createIndex($project);
            // $app['elasticsearch.manager']->syncAll($project);
        }

        return "Done!";
    }

    public function getGraph(Request $request, Application $app)
    {
        $relations = array();
        foreach ($app['contenttypes'] as $contentType) {
            foreach ($contentType->getRelations() as $i => $relation) {
                $relations[] = array(
                    'id' => $i,
                    'source' => $contentType->getKey(),
                    'target' => $relation->getOther()
                );
            }
        }

        return $this->view('layouts/graph', compact('relations'));
    }

    public function getSetproject(Request $request, Application $app, $projectId)
    {
        if ($app['config']->get('app/project')) {
            $project = $app['projects']->findByMethod('getId', $projectId);
            if ($project) {
                $app['project.service']->setCurrentProject($project);
            }
        }

        return $this->to('admin.dashboard');
    }

}
