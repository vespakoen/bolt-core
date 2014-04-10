<?php

namespace Bolt\Core\FieldType;

use Bolt\Core\App;
use Bolt\Core\Field\Field;
use Bolt\Core\Config\ConfigObject;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

use Illuminate\Support\Contracts\ArrayableInterface;

class FieldType extends ConfigObject implements ArrayableInterface
{
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

    public function toArray()
    {
        return array(
            'key' => $this->getKey(),
            'doctrine_type' => $this->getDoctrineType(),
            'migrator' => $this->getMigratorConfig()
        );
    }

    public function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", '', $this->key);

        if ($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid FieldType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    public function getMigratorConfig()
    {
        $table = new Table('test');

        $migrator = $this->migrator;
        $migrator($table, new Field($this->app, 'test', $this->app['fieldtypes']->get('string')));

        foreach ($table->getColumns() as $column) {
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
        $options = $this->getDefaultMigratorConfig();

        return function ($table, $field) use ($doctrineType, $options) {
            $table->addColumn($field->getKey(), $doctrineType, $options);
        };
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorConfig()
    {
        return array(
            'length' => 256,
            'default' => ''
        );
    }

}
