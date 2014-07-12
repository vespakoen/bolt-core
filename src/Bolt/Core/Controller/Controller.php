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

}
