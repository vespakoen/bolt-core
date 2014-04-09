<?php

namespace Bolt\Core\FieldType;

/**
 * UploadcareFieldType class
 */
class UploadcareFieldType extends FieldType {

	/**
	 * Create a new UploadcareFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'uploadcare', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
