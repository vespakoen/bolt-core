<?php

namespace Bolt\Core\Compiler;

use Composer\Autoload\ClassLoader;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;
use Layla\Cody\Compilers\Php\Core\NamespaceCompiler;

class CodyElasticsearchCompiler
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function compile()
    {
        $app = $this->app;

        foreach($app['contenttypes'] as $contentType) {
            $key = $contentType->getKey();

            $resource = $this->getRepository($contentType);

            foreach($resource->getCompilerObjects() as $compiler) {
                $path = $app['paths']['base'].'/vendor/';
                $destination = $path . $compiler->getDestination();
                $path = dirname($destination);

                if( ! is_dir($path)) {
                    `mkdir -p $path`;
                }

                if( ! file_exists($destination) || $resource->get('force', false)) {
                    file_put_contents($destination, $compiler->compile());
                }
            }
        }

        $this->register();
    }

    public function register()
    {
        $app = $this->app;

        foreach($app['contenttypes'] as $contentType) {
            $key = $contentType->getKey();
            $resource = $this->getRepository($contentType);

            $package = $this->getPackage();
            $vendor = $package->getVendor();
            $lowerVendor = strtolower($vendor);
            $name = $package->getName();
            $lowerName = strtolower($name);

            $loader = new ClassLoader;
            $loader->add($vendor.'\\'.$name.'\\', $app['paths']['base'].'/vendor/'.$lowerVendor.'/'.$lowerName.'/src');
            $loader->register();

            $this->registerRepository($key, $resource);
        }
    }

    protected function getPackage()
    {
        $config = $this->app['config'];

        $vendor = $config->get('app/package/vendor', 'MyApp');
        $name = $config->get('app/package/name', 'Domain');

        return new Package($vendor, $name);
    }

    protected function getRepository($contentType)
    {
        $package = $this->getPackage();

        $type = 'class';

        $name = $this->getRepositoryName($contentType);

        $configuration = array(
            'base' => 'Bolt.Core.Content.Elasticsearch.ElasticsearchRepository',
        );

        $compilers = array(
            'php-core'
        );

        return new Resource($package, $type, $name, $configuration, $compilers);
    }

    protected function getRepositoryName($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Repositories',
            'Elasticsearch',
            ucfirst($contentType->getKey()).'Repository'
        );

        return implode('.', $parts);
    }

    protected function registerRepository($key, $repository)
    {
        $app = $this->app;
        $namespaceCompiler = new NamespaceCompiler($repository->getName());
        $className = $namespaceCompiler->getName();
        $app['repository.elasticsearch.'.$key] = $app->share(function($app) use ($className, $key) {
            return new $className($app, $app['elasticsearch'], $app['contenttypes']->get($key));
        });
    }

}
