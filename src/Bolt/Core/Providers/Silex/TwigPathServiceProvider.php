<?php

namespace Bolt\Core\Providers\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Twig integration for Silex.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigPathServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['twig.path'] = array(
        	realpath($app['paths']['base'].'/app/views'),
            realpath($app['paths']['base'].'/vendor/vespakoen/bolt-core/src/views')
        );
    }

    public function boot(Application $app)
    {
    }
}
