<?php

namespace Bolt\Core\FieldType;

/**
 * DatetimeFieldType class
 */
class DatetimeFieldType extends FieldType {

    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'datetime';

	/**
	 * Create a new DatetimeFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'datetime', Closure $migrator = null)
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
            'notnull' => false
        );
    }

}
