<?php

namespace Bolt\Core\FieldType;

/**
 * ImageListFieldType class
 */
class ImageListFieldType extends TextFieldType
{
    /**
     * Create a new ImageListFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'imagelist', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
