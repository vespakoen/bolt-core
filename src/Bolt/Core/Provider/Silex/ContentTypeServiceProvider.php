<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ContentTypeServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerContentTypeCollection($app);
    }

    protected function registerContentTypeCollection($app)
    {
        $app['contenttypes'] = $app->share(function ($app) {
            $config = $app['config']->get('contenttypes');

            return $app['contenttypes.factory']->fromConfig($config);
        });
    }

    public function boot(Application $app)
    {
    }

}
