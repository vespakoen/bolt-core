<?php

namespace Bolt\Core\Compiler;

use Composer\Autoload\ClassLoader;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;
use Layla\Cody\Compilers\Php\Core\NamespaceCompiler;

class CodyDoctrineCompiler
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

            $generatedModel = $this->getGeneratedModel($contentType);
            $model = $this->getModel($contentType);

            foreach(array($generatedModel, $model) as $resource) {
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

            $package = $this->getPackage();
            $vendor = $package->getVendor();
            $lowerVendor = strtolower($vendor);
            $name = $package->getName();
            $lowerName = strtolower($name);

            // $app['autoloader']->add($vendor.'\\'.$name, $app['paths']['base'].'vendor/'.$lowerVendor.'/'.$lowerName.'/src');

//            $this->registerModel($key, $model);
        }
    }

    protected function getPackage()
    {
        $config = $this->app['config'];

        $vendor = $config->get('app/package/vendor', 'MyApp');
        $name = $config->get('app/package/name', 'Domain');

        return new Package($vendor, $name);
    }

    protected function getGeneratedModel($contentType)
    {
        $package = $this->getPackage();

        $type = 'model';

        $name = $this->getGeneratedModelName($contentType);

        $configuration = array(
            'force' => true,
            'comment' => 'THIS FILE IS AUTOMATICALLY GENERATED, DO NOT EDIT!!!!!!!',
            'base' => 'Bolt.Core.Storage.Doctrine.Model',
            'table' => $contentType->getKey(),
            'properties' => array(
                "table" => array(
                    "comment" => "The table associated with the model.",
                    "visibility" => "protected",
                    "value" => $contentType->getKey()
                )
            ),
            'columns' => $this->getModelColumns($contentType),
            'relations' => $this->getModelRelations($contentType),
            'methods' => $this->getModelMethods($contentType)
        );

        $compilers = array(
            'php-doctrine'
        );

        return new Resource($package, $type, $name, $configuration, $compilers);
    }

    protected function getGeneratedModelName($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Model',
            'Doctrine',
            'Generated',
            'Generated'.ucfirst($contentType->getKey())
        );

        return implode('.', $parts);
    }

    protected function getModel($contentType)
    {
        $package = $this->getPackage();

        $type = 'class';

        $name = $this->getModelName($contentType);

        $configuration = array(
            'base' => $this->getGeneratedModelName($contentType)
        );

        $compilers = array(
            'php-core'
        );

        return new Resource($package, $type, $name, $configuration, $compilers);
    }

    protected function getModelName($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Model',
            'Doctrine',
            ucfirst($contentType->getKey())
        );

        return implode('.', $parts);
    }

    protected function getModelColumns($contentType)
    {
        $columns = array();

        foreach ($contentType->getAllFields()->getDatabaseFields() as $field) {
            $type = $field->getType();

            $columns[$field->getKey()] = $field->getMigratorConfig();
        }

        // foreach ($contentType->getFields()->filterBy('multilanguage', false, false) as $field) {
        //     $type = $field->getType();

        //     $columns[$field->getKey()] = array_merge(array(
        //         'type' => $type->getType()
        //     ), $type->getOptions());
        // }

        // foreach ($contentType->getFields()->filterBy('multilanguage', true, false) as $field) {
        //     $type = $field->getType();

        //     foreach($this->app['config']->get('app/locales') as $locale => $name) {
        //         $columns[$field->getKey().'_'.$locale] = array_merge(array(
        //         'type' => $type->getType()
        //     ), $type->getOptions());
        //     }
        // }

        return $columns;
    }

    protected function getModelRelations($contentType)
    {
        $relations = array();
        foreach($contentType->getRelations() as $relation)
        {
            $relationKey = $relation->getKey();
            $otherKey = $relation->getOther();
            $otherContentType = $this->app['contenttypes']->get($otherKey);
            $relations[$relationKey] = array(
                'type' => lcfirst($this->studly($relation->getType()))
            );

            if($otherKey) {
                $relations[$relationKey]['other'] = $this->getModelName($otherContentType);
            }
        }

        return $relations;
    }

    protected function getModelMethods($contentType)
    {
        $methods = array();

        foreach ($contentType->getAllFields()->getDatabaseFields() as $field) {
            $type = $field->getType();

            // $methodName = 'get' . $this->studly($field->getKey());
            // $methods[$methodName] = array(
            //     'content' => array(
            //         'php-core' => $this->getters[$type->getKey()]
            //     )
            // );
        }

        return $methods;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    protected function registerModel($key, $model)
    {
        $app = $this->app;
        $namespaceCompiler = new NamespaceCompiler($model->getName());
        $className = $namespaceCompiler->getName();
        $app['model.'.$key] = $app->share(function($app) use ($className) {
            return new $className;
        });
    }

}
