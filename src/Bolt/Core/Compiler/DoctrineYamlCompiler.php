<?php

namespace Bolt\Core\Compiler;

use Composer\Autoload\ClassLoader;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;
use Layla\Cody\Compilers\Php\Core\NamespaceCompiler;

use Symfony\Component\Yaml\Yaml;

class DoctrineYamlCompiler extends CodyDoctrineCompiler
{
    public function compile()
    {
        $app = $this->app;

        foreach($this->compileContentTypes() as $destination => $data) {
            $path = $app['paths']['storage'].'/cache/doctrine-yml/';
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
            'id' => array(),
            'fields' => array(),
            'indexes' => array(),
            'lifecycleCallbacks' => array()
        );

        $fields = $contentType->getAllFields();
        foreach ($fields->getNonPrimaryKeyFields()->getDatabaseFields() as $field) {
            $key = $field->getKey();

            $type = $field->getType();
            $migratorConfig = $type->getMigratorConfig();

            if($field->get('index', false)) {
                $options['indexes']['idx_'.$key.'_'.$contentTypeKey] = array(
                    'columns' => array($key)
                );
            }

            $options['fields'][$key] = $migratorConfig;
        }

        foreach ($fields->getPrimaryKeyFields() as $field) {
            $type = $field->getType();
            $options['id'][$field->getKey()] = $type->getMigratorConfig();
        }

        return array($contentTypeKey => $options);
    }

}
