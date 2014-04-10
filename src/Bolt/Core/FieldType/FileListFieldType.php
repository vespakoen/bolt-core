<?php

namespace Bolt\Core\FieldType;

/**
 * FileListFieldType class
 */
class FileListFieldType extends TextFieldType
{
    /**
     * Create a new FileListFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'filelist', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
