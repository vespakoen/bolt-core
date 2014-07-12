<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Bolt\Core\Project\ProjectService;

class ProjectServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['project.service'] = $app->share(function($app) {
            return new ProjectService($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
