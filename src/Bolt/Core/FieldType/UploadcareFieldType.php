<?php

namespace Bolt\Core\FieldType;

class UploadcareFieldType extends FieldType {

    public function __construct($app, $key = 'uploadcare', $doctrineType = null, $serializer = null, Closure $migrator = null)
    {
        parent::__construct($app, $key, $doctrineType, $serializer, $migrator);
    }

}
