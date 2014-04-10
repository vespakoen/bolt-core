<?php

namespace Bolt\Core\FieldType;

/**
 * SlugFieldType class
 */
class SlugFieldType extends FieldType
{
    /**
     * Create a new SlugFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'slug', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
