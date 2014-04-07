<?php

namespace Bolt\Core\FieldType;

/**
 * DateFieldType class
 */
class DateFieldType extends FieldType {

    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'date';

	/**
	 * Create a new DateFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'date', Closure $migrator = null)
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
            'notnull' => false
        );
    }

}
