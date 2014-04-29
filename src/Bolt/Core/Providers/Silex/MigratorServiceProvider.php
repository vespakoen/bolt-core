<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Migrator\Migrator;

use Silex\Application;
use Silex\ServiceProviderInterface;

class MigratorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['migrator'] = $app->share(function($app) {
            return new Migrator($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
