<?php

namespace Bolt\Core\FieldType;

/**
 * VideoFieldType class
 */
class VideoFieldType extends TextFieldType
{
    /**
     * Create a new VideoFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'video', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
