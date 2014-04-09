<?php

namespace Bolt\Core\FieldType;

/**
 * HtmlFieldType class
 */
class HtmlFieldType extends TextFieldType {

	/**
	 * Create a new HtmlFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'html', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
