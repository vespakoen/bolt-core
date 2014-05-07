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

            $driver = $config->get('app/database/driver');
            if ($driver == 'sqlite') {
                return array(
                    'driver'    => $driver,
                    'host'      => $config->get('app/database/host', 'localhost'),
                    'database'  => $app['paths']['storage'] . '/bolt.db',
                    'username'  => $config->get('app/database/username'),
                    'password'  => $config->get('app/database/password'),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
                );
            }
            else {
                if($driver == 'postgres') {
                    $driver = 'pgsql';
                }

                return array(
                    'driver'    => $driver,
                    'host'      => $config->get('app/database/host', 'localhost'),
                    'database'  => $config->get('app/database/databasename'),
                    'username'  => $config->get('app/database/username'),
                    'password'  => $config->get('app/database/password'),
                    'charset'   => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                    'prefix'    => '',
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
        $app['eloquent']->setAsGlobal();
    }

    public function boot(Application $app)
    {
    }

}
