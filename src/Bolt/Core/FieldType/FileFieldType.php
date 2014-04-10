<?php

namespace Bolt\Core\FieldType;

/**
 * FileFieldType class
 */
class FileFieldType extends FieldType
{
    /**
     * Create a new FileFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'file', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
