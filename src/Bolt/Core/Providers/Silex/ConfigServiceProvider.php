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

class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerConfigData($app);
        $this->registerDirectories($app);
        $this->registerLocator($app);
        $this->registerLoader($app);
        $this->registerConfig($app);
    }

    protected function registerConfigData(Application $app)
    {
        $app['config.data'] = array();
    }

    protected function registerDirectories(Application $app)
    {
        $app['config.directories'] = $app->share(function ($app) {
            $paths = array();

            if (isset($app['env']) && ! is_null($app['env'])) {
                $paths[] = $app['paths']['app'].'/config/'.$app['env'];
            }

            if (isset($app['project']) && ! is_null($app['project'])) {
                $paths[] = $app['paths']['app'].'/config/'.$app['project'];
            }

            $paths[] = $app['paths']['app'].'/config/';

            return $paths;
        });
    }

    protected function registerLocator(Application $app)
    {
        $app['config.locator'] = $app->share(function ($app) {
            return new ConfigLocator($app['config.directories']);
        });
    }

    protected function registerLoader(Application $app)
    {
        $app['config.loader'] = $app->share(function ($app) {
            return new YamlConfigLoader($app['config.locator']);
        });
    }

    protected function registerConfig(Application $app)
    {
        $app['config'] = $app->share(function ($app) {
            return new Config($app['config.loader'], $app['config.files'], $app['config.data']);
        });
    }

    public function boot(Application $app)
    {
    }

}
