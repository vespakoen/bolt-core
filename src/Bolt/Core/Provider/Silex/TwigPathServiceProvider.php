<?php

namespace Bolt\Core\Provider\Silex;

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
            realpath($app['paths']['base'].'/vendor/vespakoen/bolt-core/app/views')
        );
    }

    public function boot(Application $app)
    {
    }
}
