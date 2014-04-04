<?php

namespace Bolt\Core\FieldType;

class TextFieldType extends FieldType {

    public function __construct($key = 'text', $doctrineType = null, $serializer = null, Closure $migrator = null)
    {
        parent::__construct('text', $doctrineType, $serializer, $migrator);
    }

}
