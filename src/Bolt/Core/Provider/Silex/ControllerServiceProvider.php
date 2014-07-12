<?php

namespace Bolt\Core\Provider\Silex;

use Bolt\Core\Controller\Admin;
use Bolt\Core\Controller\Frontend;
use Bolt\Core\Controller\Async;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['controller.admin'] = $app->share(function($app) {
            return new Admin($app, $app['storage.service']);
        });

        $app['controller.frontend'] = $app->share(function($app) {
            return new Frontend($app);
        });

        $app['controller.async'] = $app->share(function($app) {
            return new Async($app, $app['storage.service']);
        });
    }

    public function boot(Application $app)
    {
    }
}
