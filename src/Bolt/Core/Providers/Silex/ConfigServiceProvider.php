<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Config\Config;
use Bolt\Core\Config\ConfigLocator;
use Bolt\Core\Config\ConfigLoaderResolver;
use Bolt\Core\App\Loader\YamlAppLoader;
use Bolt\Core\FieldType\Loader\YamlFieldTypeLoader;
use Bolt\Core\ContentType\Loader\YamlContentTypeLoader;
use Bolt\Core\Extension\Loader\YamlExtensionLoader;
use Bolt\Core\Routing\Loader\YamlRoutingLoader;

use Silex\Application;
use Silex\ServiceProviderInterface;

use Symfony\Component\Config\Loader\DelegatingLoader;

class ConfigServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $this->registerDirectories($app);
        $this->registerLocator($app);
        $this->registerContentTypeLoaders($app);
        $this->registerResolver($app);
        $this->registerLoader($app);
        $this->registerConfig($app);
    }

    protected function registerDirectories(Application $app)
    {
        $app['config.directories'] = $app->share(function($app) {
            $paths = array($app['paths.config']);

            if(isset($app['env'])) {
                $paths[] = $app['paths.config'].$app['env'];
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
                new YamlAppLoader($app['config.locator']),
                new YamlContentTypeLoader($app['config.locator']),
                new YamlFieldTypeLoader($app['config.locator']),
                new YamlExtensionLoader($app['config.locator']),
                new YamlRoutingLoader($app['config.locator']),
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
