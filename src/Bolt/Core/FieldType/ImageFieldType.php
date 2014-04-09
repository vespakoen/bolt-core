<?php

namespace Bolt\Core\FieldType;

/**
 * ImageFieldType class
 */
class ImageFieldType extends FieldType {

	/**
	 * Create a new ImageFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'image', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
