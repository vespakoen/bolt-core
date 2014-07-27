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

        $controllers->get('/', 'controller.admin:getDashboard');
        $controllers->get('dashboard', 'controller.admin:getDashboard')->bind('admin.dashboard');
        $controllers->get('graph', 'controller.admin:getGraph')->bind('admin.graph');
        $controllers->get('contentaction', 'controller.admin:getContentaction')->bind('contentaction');
        $controllers->get('setproject/{projectId}', 'controller.admin:getSetproject')->bind('setproject');
        $controllers->get('{contentTypeKey}/', 'controller.admin:getOverview')->bind('overview');
        $controllers->get('{contentTypeKey}/manage', 'controller.admin:getManage')->bind('manage.new');
        $controllers->get('{contentTypeKey}/manage/{id}', 'controller.admin:getManage')->bind('manage');
        $controllers->post('{contentTypeKey}/manage', 'controller.admin:postManage');
        $controllers->post('{contentTypeKey}/manage/{id}', 'controller.admin:postManage');
        $controllers->get('{contentTypeKey}/reorder/{id}', 'controller.admin:getReorder')->bind('reorder');
        $controllers->get('{contentTypeKey}/delete/{id}', 'controller.admin:getDeletecontent')->bind('deletecontent');
        $controllers->get('reset-elasticsearch', 'controller.admin:getResetElasticsearch');

        return $controllers;
    }

    public function getDashboard(Request $request, Application $app)
    {
        if ( ! $request->query->has('offset')) {
            $request->query->set('offset', 0);
        }

        if ( ! $request->query->has('limit')) {
            $request->query->set('limit', 5);
        }

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

    public function getOverview(Request $request, Application $app, $contentTypeKey)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $contents = $this->storageService->getForListing($contentType, $request);

        return $this->view('layouts/overview', compact('contents', 'contentType'));
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
                $related->put($app[$relateKey]->getId(), $app[$relateKey]);
                $content->setAttribute('outgoing.' . $relationKey, $related);
            }
        }

        $view = $this->view('layouts/manage', compact('content', 'contentType'));

        $app['session']->getFlashBag()->clear();

        return $view;
    }

    public function postManage(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        $success = true;

        $input = $request->request->all();
        foreach ($input as $key => $items) {
            if(substr($key, 0, 1) == "_") {
                continue;
            }

            if ( ! $contentType = $app['contenttypes']->get($key)) {
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

        if ($success) {
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

        if ($isSuccessful) {
            return $this->to('overview', array(
                'contentTypeKey' => $contentTypeKey
            ));
        } else {
            return $this->back();
        }
    }

    public function getReorder(Request $request, Application $app, $contentTypeKey, $id)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        $model = $app['model.eloquent.' . $contentTypeKey];
        $weightField = $contentType->getDefaultFields()->forPurpose('weight');
        $direction = $request->get('direction');
        $currentItem = $model->find($id);

        // ignore cases we don't like
        if (
            ! $weightField || // no weight field specified @todo throw an error
            ! $currentItem // no item found that has to be moved
        ) {
            return $this->back();
        }

        $weightFieldKey = $weightField->getKey();
        $currentWeight = $currentItem->weight;

        if ($currentWeight == 0) {
            // table is fucked, we will clean up the weigths, and let the user try again
            $contents = $model->get();
            foreach ($contents as $i => $content) {
                $content->weight = $i + 1;
                $content->save();
            }

            // sync elasticsearch index
        }

        if ($direction == 'down') {
            // move next item up
            // move current item down

            $contents = $model->where('weight', '<', $currentWeight)
                ->orderBy($weightFieldKey, 'asc')
                ->get();
        } else {
            // move previous item down
            // move current item up

            $contents = $model->where('weight', '<', $currentWeight)
                ->orderBy($weightFieldKey, 'asc')
                ->get();
        }

        return $this->back();
    }

    public function getResetElasticsearch(Request $request, Application $app)
    {
        $projects = $app['projects'];

        $projectsKey = $app['config']->get('app/project/contenttype');
        $projectContentType = $app['contenttypes']->get($projectsKey);
        $project = $app['content.factory']->create(array(
            'namespace' => 'trapps',
            'unfiltered' => true
        ), $projectContentType);
        $projects->push($project);

        foreach ($projects as $project) {
            $app['elasticsearch.manager']->dropIndex($project);
            $app['elasticsearch.manager']->createIndex($project);
            $app['elasticsearch.manager']->syncAll($project);
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
