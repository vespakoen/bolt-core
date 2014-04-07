<?php

namespace Bolt\Core\FieldType;

/**
 * SelectFieldType class
 */
class SelectFieldType extends TextFieldType {

	/**
	 * Create a new SelectFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'select', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
