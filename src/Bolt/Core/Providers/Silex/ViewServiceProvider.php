<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\View\Factory\View;
use Bolt\Core\View\ViewLoader;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerViewFactories($app);
    }

    protected function registerViewFactories(Application $app)
    {
        $app['view.factory'] = $app->share(function ($app) {
            return new View($app);
        });

        $app['twig.loader'] = $app->share(function ($app) {
            return new ViewLoader($app['twig.path']);
        });
    }

    public function boot(Application $app)
    {
    }

}
