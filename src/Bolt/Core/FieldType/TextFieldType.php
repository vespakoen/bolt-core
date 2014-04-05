<?php

namespace Bolt\Core\FieldType;

class TextFieldType extends FieldType {

    public function __construct($app, $key = 'text', $doctrineType = null, $serializer = null, Closure $migrator = null)
    {
        parent::__construct($app, 'text', $doctrineType, $serializer, $migrator);
    }

}
