<?php

namespace Bolt\Core\FieldType;

/**
 * StringFieldType class
 */
class StringFieldType extends FieldType {

	/**
	 * Create a new StringFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'string', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
