<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\ContentType\Factory\ContentType;
use Bolt\Core\ContentType\Factory\ContentTypeCollection;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ContentTypeServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerContentTypeFactories($app);
        $this->registerContentTypeCollection($app);
    }

    protected function registerContentTypeFactories(Application $app)
    {
        $app['contenttype.factory'] = $app->share(function ($app) {
            return new ContentType($app);
        });

        $app['contenttypes.factory'] = $app->share(function ($app) {
            return new ContentTypeCollection($app);
        });
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
