<?php

namespace Bolt\Core\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;

use Illuminate\Support\Collection;

use Bolt\Core\Config\Object\Collection\ContentCollection;

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
        $contentTypeContent = new Collection();

        if ( ! $request->query->has('offset')) {
            $request->query->set('offset', 0);
        }

        if ( ! $request->query->has('limit')) {
            $request->query->set('limit', 5);
        }

        $contentTypes = array();
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

        if (is_null($id)) {
            $content = $app['content.factory']->create(array(), $contentType);
        } else {
            $content = $this->storageService->getForManage($contentType, $id);

            if( ! $content) {
                $app->abort(404);
            }
        }

        if ($autoRelate = $contentType->get('auto_relate')) {
            foreach ($autoRelate as $relateKey) {
                $relationKey = $app['config']->get('app/' . $relateKey . '/contenttype');
                $related = $content->getAttribute('outgoing.' . $relationKey, new ContentCollection);
                $related->put($app[$relateKey]->getId(), $app[$relateKey]);
                $content->setAttribute('outgoing.' . $relationKey, $related);
            }
        }

        return $this->view('layouts/manage', compact('content', 'contentType'));
    }

    public function postManage(Request $request, Application $app, $contentTypeKey, $id = null)
    {
        if ( ! $contentType = $app['contenttypes']->get($contentTypeKey)) {
            $app->abort(404, "Contenttype $contentTypeKey does not exist.");
        }

        if (is_null($id)) {
            $isSuccessful = $this->storageService->insert($contentType, $request);
        } else {
            $isSuccessful = $this->storageService->update($contentType, $request, $id);
        }

        if ($isSuccessful) {
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

        $isSuccessful = $this->storageService->delete($contentType, $request, $id);

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
        $namespaces = $app['projects']->lists('namespace');
        $namespaces[] = "trapps";

        foreach ($namespaces as $namespace) {
            $namespace = str_replace('.', '', $namespace);
            $app['elasticsearch.manager']->dropIndex($namespace);
            $app['elasticsearch.manager']->createIndex($namespace);
            $app['elasticsearch.manager']->syncAll($namespace);
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
            if($project) {
                $app['project.service']->setCurrentProject($project);
            }
        }

        return $this->to('admin.dashboard');
    }

}
