<?php

namespace Bolt\Core\FieldType;

/**
 * GeolocationFieldType class
 */
class GeolocationFieldType extends TextFieldType {

	/**
	 * Create a new GeolocationFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'geolocation', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
