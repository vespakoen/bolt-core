<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Compiler\CodyLaravelCompiler;
use Bolt\Core\Compiler\CodyDoctrineCompiler;
use Bolt\Core\Compiler\CodyElasticsearchCompiler;
use Bolt\Core\Compiler\DoctrineYamlCompiler;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CompilerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['compiler.cody.laravel'] = $app->share(function ($app) {
            return new CodyLaravelCompiler($app);
        });

        $app['compiler.cody.doctrine'] = $app->share(function ($app) {
            return new CodyDoctrineCompiler($app);
        });

        $app['compiler.cody.elasticsearch'] = $app->share(function ($app) {
            return new CodyElasticsearchCompiler($app);
        });

        $app['compiler.doctrine.yaml'] = $app->share(function ($app) {
            return new DoctrineYamlCompiler($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
