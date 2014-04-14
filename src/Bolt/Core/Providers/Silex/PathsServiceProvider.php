<?php

namespace Bolt\Core\Providers\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class PathsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['paths.root'] = array_get($_SERVER, 'DOCUMENT_ROOT', realpath(__DIR__.'/../../../../../../../../')).'/';
        $app['paths.config'] = $app['paths.root'].'app/config';
        $app['paths.cache'] = $app['paths.root'].'app/cache';
        $app['paths.databases'] = $app['paths.root'].'app/databases';
    }

    public function boot(Application $app)
    {
    }

}
