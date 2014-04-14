<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Serializer\CodyLaravelSerializer;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SerializerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['serializer.cody.laravel'] = $app->share(function ($app) {
            return new CodyLaravelSerializer($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
