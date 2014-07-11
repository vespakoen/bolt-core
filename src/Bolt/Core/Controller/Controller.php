<?php

namespace Bolt\Core\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

use Illuminate\Support\Contracts\ArrayableInterface;

class Controller
{
    protected function back()
    {
        $request = $this->app['request'];
        $url = $request->headers->get('referer');
        return $this->app->redirect($url);
    }

    protected function to($name, $arguments = array())
    {
        $url = $this->app['url_generator']->generate($name, $arguments);
        return $this->app->redirect($url);
    }

    protected function view($file, $context = array())
    {
        return $this->app['view.factory']->create($file, $context)
            ->render();
    }

    protected function json($data)
    {
        if ($data instanceof ArrayableInterface) {
            $data = $data->toArray();
        }

        return new JsonResponse($data);
    }

    public function getReadRepository($contentType)
    {
        // $this->app['repository.resolver.read']->resolve($contentType);
        return $this->app['repository.eloquent.' . $contentType->getKey()];
    }

    protected function getWheres($contentType, $getAll = false)
    {
        if ($getAll && $this->app['user']->hasRole('ROLE_ADMIN')) {
            return array();
        }

        $projectKey = $this->app['config']->get('app/project/contenttype');
        if ($contentType->getKey() == $projectKey) {
            return array(
                $projectKey . '.id' => $this->app['session']->get('project_id')
            );
        }

        if ($contentType->get('filter', true) == false) {
            return array();
        }

        return array(
            'incoming.to_id' => $this->app['session']->get('project_id')
        );
    }

}
