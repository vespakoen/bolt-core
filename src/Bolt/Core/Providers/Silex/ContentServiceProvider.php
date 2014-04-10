<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Content\Factory\Content;
use Bolt\Core\Content\Factory\ContentCollection;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ContentServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $this->registerContentFactories($app);
    }

    protected function registerContentFactories(Application $app)
    {
        $app['content.factory'] = $app->share(function($app) {
            return new Content($app);
        });

        $app['contents.factory'] = $app->share(function($app) {
            return new ContentCollection($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
