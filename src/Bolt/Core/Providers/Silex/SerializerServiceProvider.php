<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Serializer\CodySerializer;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SerializerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['serializer.cody'] = $app->share(function ($app) {
            return new CodySerializer($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
