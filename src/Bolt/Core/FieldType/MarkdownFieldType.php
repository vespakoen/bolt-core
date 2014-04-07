<?php

namespace Bolt\Core\FieldType;

use Doctrine\DBAL\Platforms\SqlitePlatform;

/**
 * MarkdownFieldType class
 */
class MarkdownFieldType extends TextFieldType {

	/**
	 * Create a new MarkdownFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'markdown', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
