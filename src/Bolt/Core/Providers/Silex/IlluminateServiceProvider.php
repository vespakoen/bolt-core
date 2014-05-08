<?php

namespace Bolt\Core\Providers\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Validation\Factory;

class IlluminateServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $core = $app;
        $app['illuminate'] = new Container;

        $app['illuminate']->bindShared('validator', function($app) use ($core)
        {
            $validator = new Factory($core['translator'], $app);

            // The validation presence verifier is responsible for determining the existence
            // of values in a given data collection, typically a relational database or
            // other persistent data stores. And it is used to check for uniqueness.
            if (isset($app['validation.presence']))
            {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            return $validator;
        });
    }

    public function boot(Application $app)
    {
    }
}
