<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Controller\Admin;
use Bolt\Controller\Async;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerAdminController($app);
    }

    protected function registerAdminController(Application $app)
    {
        $app['controller.admin'] = $app->share(function($app) {
            return new Admin($app);
        });

        $app['controller.async'] = $app->share(function($app) {
            return new Async($app);
        });
    }

    public function boot(Application $app)
    {
    }

}




