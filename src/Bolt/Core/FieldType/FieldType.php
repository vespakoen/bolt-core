<?php

namespace Bolt\Core\FieldType;

use Bolt\Core\Field\Field;

use Doctrine\DBAL\Schema\Table;

use Illuminate\Support\Contracts\ArrayableInterface;

class FieldType implements ArrayableInterface {

    protected $key;

    protected $doctrineType;

    protected $serializer;

    protected $migrator;

    public function __construct($key, $doctrineType = null, $serializer = null, Closure $migrator = null)
    {
        $this->key = $key;
        $this->doctrineType = is_null($doctrineType) ? $this->getDefaultDoctrineType() : $doctrineType;
        $this->serializer = is_null($serializer) ? $this->getDefaultSerializer() : $serializer;
        $this->migrator = is_null($migrator) ? $this->getDefaultMigrator() : $migrator;
    }

    public static function fromConfig($key, $config = array())
    {
        static::validate($key, $config);

        $doctrineType = array_get($config, 'doctrine_type');
        $serializer = array_get($config, 'serializer');
        $migrator = array_get($config, 'migrator');

        if(!is_null($migrator)) {
            $migrator = function($table, $field) use ($migrator) {
                $key = $field->getKey();

                $type = array_get($migrator, 'type', 'string');
                $options = array_get($migrator, 'options', array());

                $table->addColumn($key, $type, $options);
            };
        }

        return new static($key, $doctrineType, $serializer, $migrator);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", '', $key);

        if($key !== $cleaned) {
            throw new InvalidArgumentException(sprintf('Invalid FieldType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }
    }

    public function getSerializer()
    {
        $serializerClass = $this->serializer;

        return new $serializerClass;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getDoctrineType()
    {
        return $this->doctrineType;
    }

    public function getMigrator()
    {
        return $this->migrator;
    }

    public function toArray()
    {
        return array(
            'doctrine_type' => $this->doctrineType,
            'serializer' => $this->serializer,
            'migrator' => $this->getMigratorConfig()
        );
    }

    protected function getMigratorConfig()
    {
        $table = new Table('test');

        $migrator = $this->migrator;
        $migrator($table, new Field('test'));

        foreach($table->getColumns() as $column)
        {
            $config = $column->toArray();

            $result = array(
                'type' => get_class($config['type']),
            );
            unset($config['type']);
            unset($config['name']);

            $result['options'] = $config;

            return $result;
        }

        return array();
    }

    protected function getDefaultDoctrineType()
    {
        return 'Doctrine\DBAL\Types\StringType';
    }

    protected function getDefaultSerializer()
    {
        return 'Bolt\Core\FieldType\Serializer\PassthroughSerializer';
    }

    protected function getDefaultMigrator()
    {
        return function($table, $field) {
            $table->addColumn($field->getKey(), 'string', array('length' => 256, 'default' => ''));
        };
    }

}
