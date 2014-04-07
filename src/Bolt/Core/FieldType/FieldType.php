<?php

namespace Bolt\Core\FieldType;

use Bolt\Core\App;
use Bolt\Core\Field\Field;
use Bolt\Core\Config\ConfigObject;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

use Illuminate\Support\Contracts\ArrayableInterface;

class FieldType extends ConfigObject implements ArrayableInterface {

    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'string';

    protected $objectType = 'fieldtype';

    protected $key;

    protected $migrator;

    public function __construct($app, $key, Closure $migrator = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->migrator = is_null($migrator) ? $this->getDefaultMigrator() : $migrator;
        $this->options = $options;

        $this->validate();
    }

    public static function fromConfig($key, $config = array())
    {
        $app = App::instance();

        if( ! is_string($config)) {
            $fieldClass = $config;

            if( ! class_exists($fieldClass)) {
                $app['notify']->error('Unknown class for fieldtype: '.$fieldClass);
            }

            return new $fieldClass($app);
        }

        $migrator = array_get($config, 'migrator');

        if(!is_null($migrator)) {
            $migrator = function($table, $field) use ($migrator) {
                $key = $field->getKey();

                $type = array_get($migrator, 'type', 'string');
                $options = array_get($migrator, 'options', array());

                $table->addColumn($key, $type, $options);
            };
        }

        return new static($app, $key, $migrator);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getMigrator()
    {
        return $this->migrator;
    }

    public function getDoctrineType()
    {
        return $this->doctrineType;
    }

    public function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", '', $this->key);

        if($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid FieldType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    public function getMigratorConfig()
    {
        $table = new Table('test');

        $migrator = $this->migrator;
        $migrator($table, new Field($this->app, 'test'));

        foreach($table->getColumns() as $column)
        {
            $config = $column->toArray();

            $result = array(
                'type' => $config['type']->getName(),
            );
            unset($config['type']);
            unset($config['name']);

            $result['options'] = $config;

            return $result;
        }

        return array();
    }

    /**
     * Get the migrator
     *
     * @return string
     */
    protected function getDefaultMigrator()
    {
        $doctrineType = $this->getDoctrineType();
        $defaultMigratorOptions = $this->getDefaultMigratorOptions();

        return function($table, $field) use ($doctrineType, $defaultMigratorOptions) {
            $options = array_merge($defaultMigratorOptions, $field->getMigratorOptions());
            $table->addColumn($field->getKey(), $doctrineType, $options);
        };
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorOptions()
    {
        return array(
            'length' => 256,
            'default' => ''
        );
    }

}
