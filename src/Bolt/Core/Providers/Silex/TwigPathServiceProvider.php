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
        	$app['paths.root'].'vendor/vespakoen/bolt-core/app/views',
            $app['paths.root'].'vendor/vespakoen/bolt-core/src/views'
        );
    }

    public function boot(Application $app)
    {
    }
}
