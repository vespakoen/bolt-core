<?php

namespace Bolt\Core\FieldType;

/**
 * TextareaFieldType class
 */
class TextareaFieldType extends TextFieldType {

	/**
	 * Create a new TextareaFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'textarea', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
