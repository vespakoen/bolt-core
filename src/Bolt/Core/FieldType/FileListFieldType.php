<?php

namespace Bolt\Core\FieldType;

use Doctrine\DBAL\Platforms\SqlitePlatform;

/**
 * FileListFieldType class
 */
class FileListFieldType extends TextFieldType {

	/**
	 * Create a new FileListFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'filelist', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
