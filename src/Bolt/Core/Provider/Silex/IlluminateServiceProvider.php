<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\Container\Container;
use Illuminate\Validation\Factory;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\MemcachedConnector;

class IlluminateServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $core = $app;

        $app['illuminate'] = new Container;

        $app['illuminate']['config'] = array(
            'cache.driver' => 'memcached',
            'cache.memcached' => array(
                array(
                    'host' => 'localhost',
                    'port' => '11211'
                )
            )
        );

        $app['illuminate']->bindShared('validator', function($app) use ($core) {
            $validator = new Factory($core['translator'], $app);

            // The validation presence verifier is responsible for determining the existence
            // of values in a given data collection, typically a relational database or
            // other persistent data stores. And it is used to check for uniqueness.
            if (isset($app['validation.presence'])) {
                $validator->setPresenceVerifier($app['validation.presence']);
            }

            return $validator;
        });

        $app['illuminate']->bindShared('cache', function($app) {
            return new CacheManager($app);
        });

        $app['illuminate']->bindShared('cache.store', function($app) {
            return $app['cache']->driver();
        });

        $app['illuminate']->bindShared('memcached.connector', function() {
            return new MemcachedConnector;
        });
    }

    public function boot(Application $app)
    {
    }
}
