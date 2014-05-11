<?php

namespace Bolt\Core\Support;

use InvalidArgumentException;

use Bolt\Core\App;

use Illuminate\Support\MessageBag;

class Notify
{
    protected $notifications = array();

    protected $errors;

    public function __construct(App $app)
    {
        $this->app = $app;

        $this->app->after(function($request, $response) use ($app) {
            $flashBag = $app['session']->getFlashBag()->clear();
        });

        $this->errors = new MessageBag();
    }

    public function error($developerError, $userError = null)
    {
        $devMode = $this->app['config']->get('app/debug', false);

        if ($devMode) {
            throw new InvalidArgumentException($developerError);
        } elseif (!is_null($userError)) {
            $this->notifications[] = $userError;
        }
    }

    public function notifications()
    {
        return $this->notifications;
    }

    public function errors()
    {
        return $this->errors;
    }

    public function mergeErrors($errors)
    {
        $this->errors->merge($errors);
    }

}
