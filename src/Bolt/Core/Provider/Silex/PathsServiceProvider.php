<?php

namespace Bolt\Core\Provider\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class PathsServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['paths']['base'] = array_get($_SERVER, 'DOCUMENT_ROOT', realpath(__DIR__.'/../../../../../../../../')).'/';
        $app['paths']['config'] = $app['paths']['base'].'app/config';
        $app['paths.cache'] = $app['paths']['base'].'app/cache';
        $app['paths.databases'] = $app['paths']['base'].'app/databases';
    }

    public function boot(Application $app)
    {
    }

}
