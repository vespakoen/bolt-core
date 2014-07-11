<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Bolt\Core\Storage\StorageService;

class StorageServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['storage.service'] = $app->share(function($app) {
            return new StorageService($app, $app['dispatcher']);
        });
    }

    public function boot(Application $app)
    {
    }

}
