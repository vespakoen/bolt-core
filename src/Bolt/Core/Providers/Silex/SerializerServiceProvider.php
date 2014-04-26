<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Serializer\CodyLaravelSerializer;
use Bolt\Core\Serializer\CodyDoctrineSerializer;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SerializerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['serializer.cody.laravel'] = $app->share(function ($app) {
            return new CodyLaravelSerializer($app);
        });

        $app['serializer.cody.doctrine'] = $app->share(function ($app) {
            return new CodyDoctrineSerializer($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
