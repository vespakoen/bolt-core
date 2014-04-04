<?php

namespace Bolt\Core\FieldType;

class UploadcareFieldType extends FieldType {

    public function __construct($key = 'uploadcare', $doctrineType = null, $serializer = null, Closure $migrator = null)
    {
        parent::__construct($key, $doctrineType, $serializer, $migrator);
    }

}
