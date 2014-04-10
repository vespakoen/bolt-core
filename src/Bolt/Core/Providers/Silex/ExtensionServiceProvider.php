<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Extension\ExtensionCollection;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerExtensionCollection($app);
    }

    protected function registerExtensionCollection(Application $app)
    {
        $app['extensions'] = $app->share(function ($app) {
            return new ExtensionCollection;
        });
    }

    public function boot(Application $app)
    {
    }

}
