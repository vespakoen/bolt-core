<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldTypeServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerDefaultFieldTypes($app);
    }

    protected function registerDefaultFieldTypes($app)
    {
        $app['fieldtypes'] = $app->share(function($app) {
            $config = $app['config']->get('fieldtypes');
            return $app['fieldtypes.factory']->fromConfig($config);
        });
    }

    public function boot(Application $app)
    {
    }

}
