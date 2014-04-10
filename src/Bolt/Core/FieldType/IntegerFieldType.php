<?php

namespace Bolt\Core\FieldType;

/**
 * IntegerFieldType class
 */
class IntegerFieldType extends FieldType
{
    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'integer';

    /**
     * Create a new IntegerFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'integer', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorConfig()
    {
        return array(
            'default' => 0
        );
    }

}
