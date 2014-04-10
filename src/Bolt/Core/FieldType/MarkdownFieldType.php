<?php

namespace Bolt\Core\FieldType;

/**
 * MarkdownFieldType class
 */
class MarkdownFieldType extends TextFieldType
{
    /**
     * Create a new MarkdownFieldType instance
     *
     * @param $app \Silex\Application
     * @param $key string
     * @param $serializer string
     * @param $migrator Closure
     */
    public function __construct($app, $key = 'markdown', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

}
