<?php

namespace Bolt\Core\FieldType;

/**
 * VideoFieldType class
 */
class TemplateSelectFieldType extends FieldType {

	/**
	 * Create a new VideoFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'templateselect', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
