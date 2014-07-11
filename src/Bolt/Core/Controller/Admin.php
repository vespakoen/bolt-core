<?php

namespace Bolt\Core\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        $app->before(function($request) use ($app) {
            $segments = explode('/', trim($request->getPathInfo(), '/'));
            $segments = array_filter($segments, function($v) { return $v != ''; });
            $segment = array_get($segments, 0);
            if($segment !== $app['config']->get('app/branding/path')) {
                return;
            }

            // Require login. This should never actually cause access to be denied,
            // but it causes a login form to be rendered if the viewer is not logged in.
            if( ! in_array($request->get('_route'), array('user.login', 'user.register', 'user.logout'))) {
                if (!$app['user']) {
                    throw new AccessDeniedException();
                }

                if ($app['config']->get('app/project')) {
                    $app['projects'] = $app['user']->getProjects();
                    if ( ! $app['session']->has('project_id') || ! $app['projects']->has($app['session']->get('project_id'))) {
                        $project = $app['projects']->first();
                        $app['session']->set('project_id', $project->get('id'));
                        $app['session']->set('project_namespace', str_replace('.', '', $project->get('namespace')));
                    }
                }
            }
        });

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

        $contentTypes = array();
        foreach ($app['contenttypes'] as $contentType) {
            // skip stuff we don't want the user to see
            $role = $contentType->get('role', 'ROLE_USER');
            if ( ! $app['user']->hasRole($role) || $contentType->get('system', false) == true) continue;

            $defaultFields = $contentType->getDefaultFields();
            $defaultSort = $defaultFields->forPurpose('datechanged')->getKey();
            $sort = $request->get('sort', $defaultSort);
            $search = $request->get('search', null);

            $repository = $this->getReadRepository($contentType);

            $wheres = $this->getWheres($contentType);
            $contents = $repository->get($wheres, true, $sort, 'asc', 0, 5, $search);

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

        $defaultFields = $contentType->getDefaultFields();
        $defaultSort = $defaultFields->forPurpose('datechanged')->getKey();
        $sort = $request->get('sort', $defaultSort);
        $order = $request->get('order', 'desc');
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 100);
        $search = $request->get('search', null);

        $repository = $this->getReadRepository($contentType);

        $wheres = $this->getWheres($contentType);
        $contents = $repository->get($wheres, false, $sort, $order, $offset, $limit, $search);

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
            $repository = $this->getReadRepository($contentType);
            $content = $repository->find($id);

            if( ! $content) {
                $app->abort(404);
            }
        }

        // make sure the current project is available in the project relations
        if ($projectKey = $app['config']->get('app/project/contenttype')) {
            $projects = $content->getAttribute('outgoing.' . $projectKey, new ContentCollection);
            $projectId = $app['session']->get('project_id');
            $currentAppIds = $projects->keys();
            if ( ! in_array($projectId, $currentAppIds) && $contentType->get('auto_link_to_project', true)) {
                $projects->put($projectId, $app['projects']->get($projectId));
                $content->setAttribute('outgoing.' . $projectKey, $projects);
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
            // echo $contentType->getName().'<br>';
            foreach ($contentType->getRelations() as $i => $relation) {
                // echo " - " . $relation->getOther().($relation->get('inverted', false) ? ' (inverted)' : '').'<br>';
                $relations[] = array(
                    'id' => $i,
                    'source' => $contentType->getKey(),
                    'target' => $relation->getOther()
                );
            }
            // echo "<br>";
        }

        return $this->view('layouts/graph', compact('relations'));
    }

    public function getSetproject(Request $request, Application $app, $projectId)
    {
        if ($app['config']->get('app/project')) {
            $projects = $app['projects']->filterByAttribute('id', $projectId);
            if( ! $projects->isEmpty()) {
                $project = $projects->first();
                $app['session']->set('project_id', $project->get('id'));
                $app['session']->set('project_namespace', str_replace('.', '', $project->get('namespace')));
            }
        }

        return $this->to('admin.dashboard');
    }

}
