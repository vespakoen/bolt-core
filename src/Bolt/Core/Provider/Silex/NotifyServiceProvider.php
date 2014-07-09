<?php

namespace Bolt\Core\Provider\Silex;

use Bolt\Core\Support\Notify;

use Silex\Application;
use Silex\ServiceProviderInterface;

class NotifyServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['notify'] = $app->share(function ($app) {
            return new Notify($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
