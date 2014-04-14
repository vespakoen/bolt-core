<?php

namespace Bolt\Core\FieldType;

/**
 * UploadcareFieldType class
 */
class UploadcareFieldType extends FieldType
{
    /**
     * Create a new UploadcareFieldType instance
     *
     * @param $app \Silex\Application
     */
    public function __construct($app)
    {
        parent::__construct($app, 'uploadcare');
    }

}
