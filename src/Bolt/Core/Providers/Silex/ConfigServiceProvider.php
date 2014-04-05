<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Config\Config;
use Bolt\Core\Config\ConfigLocator;
use Bolt\Core\Config\ConfigLoaderResolver;
use Bolt\Core\Config\Loader\YamlConfigLoader;
use Bolt\Core\Config\Loader\YamlSerializerLoader;

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
        $this->registerConfigData($app);
        $this->registerDirectories($app);
        $this->registerLocator($app);
        $this->registerConfigLoaders($app);
        $this->registerResolver($app);
        $this->registerLoaders($app);
        $this->registerConfig($app);
    }

    protected function registerConfigData(Application $app) {
        $app['config.data'] = array();
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

    protected function registerConfigLoaders(Application $app)
    {
        $app['config.loaders.raw'] = $app->share(function($app) {
            return array(
                new YamlConfigLoader($app['config.locator']),
            );
        });

        $app['config.loaders.objectified'] = $app->share(function($app) {
            return array(
                new YamlAppLoader($app['config.locator']),
                new YamlSerializerLoader($app['config.locator']),
                new YamlContentTypeLoader($app['config.locator']),
                new YamlFieldTypeLoader($app['config.locator']),
                new YamlExtensionLoader($app['config.locator']),
                new YamlRoutingLoader($app['config.locator']),
            );
        });
    }

    protected function registerResolver(Application $app)
    {
        $app['config.resolver.raw'] = $app->share(function($app) {
            return new ConfigLoaderResolver(
                $app['config.locator'],
                $app['config.loaders.raw']
            );
        });

        $app['config.resolver.objectified'] = $app->share(function($app) {
            return new ConfigLoaderResolver(
                $app['config.locator'],
                $app['config.loaders.objectified']
            );
        });
    }

    protected function registerLoaders(Application $app)
    {
        $app['config.loader.raw'] = $app->share(function($app) {
            return new DelegatingLoader($app['config.resolver.raw']);
        });

        $app['config.loader.objectified'] = $app->share(function($app) {
            return new DelegatingLoader($app['config.resolver.objectified']);
        });
    }

    protected function registerConfig(Application $app)
    {
        $app['config'] = $app->share(function($app) {
            return new Config($app, $app['config.loader.raw'], $app['config.loader.objectified'], $app['config.files'], $app['config.data']);
        });
    }

    public function boot(Application $app)
    {
    }

}
