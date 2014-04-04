<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Field\FieldCollection;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $this->registerFieldCollection($app);
    }

    protected function registerFieldCollection(Application $app)
    {
        $app['fields'] = $app->share(function($app) {
            return new FieldCollection;
        });
    }

    public function boot(Application $app)
    {
    }

}
