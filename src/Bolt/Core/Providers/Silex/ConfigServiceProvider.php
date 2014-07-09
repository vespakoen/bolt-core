<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Config\Config;
use Bolt\Core\Config\ConfigLocator;
use Bolt\Core\Config\ConfigLoaderResolver;
use Bolt\Core\Config\Loader\YamlConfigLoader;
use Bolt\Core\Config\Loader\YamlSerializerLoader;

use Bolt\Core\Config\Object\Collection\Factory\ContentTypeCollection;
use Bolt\Core\Config\Object\Factory\ContentType;
use Bolt\Core\Config\Object\Collection\Factory\FieldCollection;
use Bolt\Core\Config\Object\Factory\Field;
use Bolt\Core\Config\Object\Collection\Factory\FieldTypeCollection;
use Bolt\Core\Config\Object\Factory\FieldType;
use Bolt\Core\Config\Object\Collection\Factory\RelationCollection;
use Bolt\Core\Config\Object\Factory\Relation;
use Bolt\Core\Config\Object\Collection\Factory\ContentCollection;
use Bolt\Core\Config\Object\Factory\Content;

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
        $this->registerConfigObjectFactories($app);
    }

    protected function registerConfigData(Application $app)
    {
        $app['config.data'] = array();
    }

    protected function registerDirectories(Application $app)
    {
        $app['config.directories'] = $app->share(function ($app) {
            $paths = array();

            if (isset($app['project']) && ! is_null($app['project'])) {
                if (isset($app['env']) && ! is_null($app['env'])) {
                    $paths[] = $app['paths']['app'].'/config/'.$app['project'].'/'.$app['env'];
                }

                $paths[] = $app['paths']['app'].'/config/'.$app['project'];
            }

            if (isset($app['env']) && ! is_null($app['env'])) {
                $paths[] = $app['paths']['app'].'/config/'.$app['env'];
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

    protected function registerConfigObjectFactories(Application $app)
    {
        $app['contenttype.factory'] = $app->share(function ($app) {
            return new ContentType($app);
        });

        $app['contenttypes.factory'] = $app->share(function ($app) {
            return new ContentTypeCollection($app);
        });

        $app['field.factory'] = $app->share(function ($app) {
            return new Field($app);
        });

        $app['fields.factory'] = $app->share(function ($app) {
            return new FieldCollection($app);
        });

        $app['fieldtype.factory'] = $app->share(function ($app) {
            return new FieldType($app);
        });

        $app['fieldtypes.factory'] = $app->share(function ($app) {
            return new FieldTypeCollection($app);
        });

        $app['relation.factory'] = $app->share(function ($app) {
            return new Relation($app);
        });

        $app['relations.factory'] = $app->share(function ($app) {
            return new RelationCollection($app);
        });

        $app['content.factory'] = $app->share(function ($app) {
            return new Content($app);
        });

        $app['contents.factory'] = $app->share(function ($app) {
            return new ContentCollection($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
