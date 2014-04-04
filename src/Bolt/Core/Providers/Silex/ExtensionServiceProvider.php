<?php

namespace Bolt\Core\Providers\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ExtensionServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $this->registerExtensions($app);
    }

    protected function registerDirectories(Application $app)
    {
        $app['config.directories'] = $app->share(function($app) {
            $paths = array($app['paths.config']);

            if(isset($app['env'])) {
                $paths[] = $app['paths.config'].$app['env']);
            }

            return $paths;
        });
    }

    protected function registerLocator(Application $app)
    {
        $app['config.locator'] = $app->share(function($app) {
            return new ConfigLocator($app['config.directories']);
        });
    }

    protected function registerContentTypeLoaders(Application $app)
    {
        $app['config.loaders'] = $app->share(function($app) {
            return array(
                new YamlContentTypeLoader($app['config.locator']),
                new YamlFieldTypeLoader($app['config.locator']),
            );
        });
    }

    protected function registerResolver(Application $app)
    {
        $app['config.resolver'] = $app->share(function($app) {
            return new ConfigLoaderResolver(
                $app['config.locator'],
                $app['config.loaders']
            );
        });
    }

    protected function registerLoader(Application $app)
    {
        $app['config.loader'] = $app->share(function($app) {
            return new DelegatingLoader($app['config.resolver']);
        });
    }

    protected function registerConfig(Application $app)
    {
        $app['config'] = $app->share(function($app) {
            return new Config($app, $app['config.loader']);
        });
    }

    public function boot(Application $app)
    {
    }

}
