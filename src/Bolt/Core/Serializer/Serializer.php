<?php

namespace Bolt\Core\Serializer;

use Composer\Autoload\ClassLoader;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;
use Layla\Cody\Compilers\Php\Core\NamespaceCompiler;

class Serializer
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function getPackage()
    {
        $config = $this->app['config'];

        $vendor = $config->get('app/package/vendor', 'MyApp');
        $name = $config->get('app/package/name', 'Domain');

        return new Package($vendor, $name);
    }

    protected function getGeneratedModelName($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Models',
            'Generated',
            'Generated'.ucfirst($contentType->getKey())
        );

        return implode('.', $parts);
    }

    protected function getModelName($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Models',
            ucfirst($contentType->getKey())
        );

        return implode('.', $parts);
    }

    protected function getModelColumns($contentType)
    {
        $columns = array();

        foreach ($contentType->getDefaultFields() as $field) {
            $config = $field->getType()->getMigratorConfig();

            $columns[$field->getKey()] = array_merge(array(
                'type' => $config['type']
            ), $config['options']);
        }

        foreach ($contentType->getFields()->filterBy('multilanguage', false, false) as $field) {
            $config = $field->getType()->getMigratorConfig();

            $columns[$field->getKey()] = array_merge(array(
                'type' => $config['type']
            ), $config['options']);
        }

        foreach ($contentType->getFields()->filterBy('multilanguage', true, false) as $field) {
            $config = $field->getType()->getMigratorConfig();

            foreach($this->app['config']->get('app/locales') as $locale => $name) {
                $columns[$field->getKey().'_'.$locale] = array_merge(array(
                    'type' => $config['type']
                ), $config['options']);
            }
        }

        return $columns;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public function studly($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    protected function registerResource($key, $model)
    {
        $app = $this->app;
        $namespaceCompiler = new NamespaceCompiler($model->getName());
        $className = $namespaceCompiler->getName();
        $app['model.'.$key] = $app->share(function($app) use ($className) {
            return new $className;
        });
    }

}
