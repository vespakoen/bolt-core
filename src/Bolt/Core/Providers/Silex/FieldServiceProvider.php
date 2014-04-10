<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Field\Factory\FieldCollection;
use Bolt\Core\Field\Factory\Field;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerFieldFactories($app);
    }

    protected function registerFieldFactories(Application $app)
    {
        $app['field.factory'] = $app->share(function ($app) {
            return new Field($app);
        });

        $app['fields.factory'] = $app->share(function ($app) {
            return new FieldCollection($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
