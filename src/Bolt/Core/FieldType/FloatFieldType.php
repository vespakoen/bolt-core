<?php

namespace Bolt\Core\FieldType;

/**
 * FloatFieldType class
 */
class FloatFieldType extends FieldType {

    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'float';

	/**
	 * Create a new FloatFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'float', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorOptions()
    {
        return array(
            'default' => 0
        );
    }

}
