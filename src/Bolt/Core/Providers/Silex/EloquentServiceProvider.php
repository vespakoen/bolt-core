<?php

namespace Bolt\Core\Providers\Silex;

use Illuminate\Database\Capsule\Manager as Capsule;

use Silex\Application;
use Silex\ServiceProviderInterface;

class EloquentServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->setupEloquentConnection($app);
        $this->registerEloquentCapsule($app);
        $this->bootEloquent($app);
    }

    protected function setupEloquentConnection(Application $app)
    {
        $app['eloquent.connection'] = $app->share(function ($app) {
            $config = $app['config'];

            $driver = $config->getRaw('app/database/driver');
            if ($driver == 'sqlite') {
                return array(
                    'driver'    => $driver,
                    'host'      => $config->getRaw('app/database/host', 'localhost'),
                    'database'  => $app['paths.root'] . 'app/database/bolt.db',
                    'username'  => $config->getRaw('app/database/username'),
                    'password'  => $config->getRaw('app/database/password'),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => 'bolt_',
                );
            } else {
                return array(
                    'driver'    => $driver,
                    'host'      => $config->getRaw('app/database/host', 'localhost'),
                    'database'  => $config->getRaw('app/database/databasename'),
                    'username'  => $config->getRaw('app/database/username'),
                    'password'  => $config->getRaw('app/database/password'),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => 'bolt_',
                );
            }
        });
    }

    protected function registerEloquentCapsule(Application $app)
    {
        $app['eloquent'] = $app->share(function ($app) {
            $capsule = new Capsule;

            $capsule->addConnection($app['eloquent.connection']);

            return $capsule;
        });
    }

    protected function bootEloquent($app)
    {
        $app['eloquent']->bootEloquent();
    }

    public function boot(Application $app)
    {
    }

}
