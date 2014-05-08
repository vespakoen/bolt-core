<?php

namespace Bolt\Core\Providers\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Illuminate\Validation\Factory;

class IlluminateValidationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['validator.factory'] = $app->share(function ($app) {
            $validator = new Factory($app['translator'], $app);

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
