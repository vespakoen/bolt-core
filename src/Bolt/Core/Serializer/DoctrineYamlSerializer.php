<?php

namespace Bolt\Core\Serializer;

use Composer\Autoload\ClassLoader;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;
use Layla\Cody\Compilers\Php\Core\NamespaceCompiler;

use Symfony\Component\Yaml\Yaml;

class DoctrineYamlSerializer extends Serializer
{
    public function serialize()
    {
        $app = $this->app;

        foreach($this->compileContentTypes() as $destination => $data) {
            $path = $app['paths']['base'].'cache/doctrine-yml/';
            $destination = $path . $destination;

            if( ! is_dir($path)) {
                `mkdir -p $path`;
            }

            file_put_contents($destination, Yaml::dump($data, 10));
        }
    }

    protected function compileContentTypes()
    {
        $package = $this->getPackage();
        $vendor = $package->getVendor();
        $name = $package->getName();

        $results = array();
        foreach($this->app['contenttypes'] as $contentType) {
            $destination = $this->getModelName($contentType).'.dcm.yml';

            $results[$destination] = $this->compileContentType($contentType);
        }

        return $results;
    }

    protected function compileContentType($contentType)
    {
        $contentTypeKey = str_replace('.', '\\', $this->getModelName($contentType));

        $options = array(
            'type' => 'entity',
            'table' => $contentType->getTableName(),
            'fields' => array(),
            'indexes' => array(),
            'lifecycleCallbacks' => array()
        );

        foreach ($contentType->getFields() as $field) {
            $key = $field->getKey();

            $migratorConfig = $field->getType()->getMigratorConfig();
            $fieldOptions = $migratorConfig['options'];

            if($field->getOption('index', false)) {
                $options['indexes']['idx_'.$key.'_'.$contentTypeKey] = array(
                    'columns' => array($key)
                );
            }

            $options['fields'][$key] = $fieldOptions;
        }

        return array($contentTypeKey => $options);
    }

}
