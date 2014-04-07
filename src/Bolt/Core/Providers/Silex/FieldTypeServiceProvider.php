<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\FieldType\FieldTypeCollection;
use Bolt\Core\FieldType\FieldType;

use Bolt\Core\FieldType\TextFieldType;
use Bolt\Core\FieldType\ImageFieldType;
use Bolt\Core\FieldType\UploadcareFieldType;
use Bolt\Core\FieldType\SlugFieldType;
use Bolt\Core\FieldType\HtmlFieldType;
use Bolt\Core\FieldType\VideoFieldType;
use Bolt\Core\FieldType\TemplateSelectFieldType;
use Bolt\Core\FieldType\GeolocationFieldType;
use Bolt\Core\FieldType\ImageListFieldType;
use Bolt\Core\FieldType\FileFieldType;
use Bolt\Core\FieldType\FileListFieldType;
use Bolt\Core\FieldType\CheckboxFieldType;
use Bolt\Core\FieldType\MarkdownFieldType;
use Bolt\Core\FieldType\DatetimeFieldType;
use Bolt\Core\FieldType\DateFieldType;
use Bolt\Core\FieldType\IntegerFieldType;
use Bolt\Core\FieldType\FloatFieldType;
use Bolt\Core\FieldType\SelectFieldType;
use Bolt\Core\FieldType\TextareaFieldType;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldTypeServiceProvider implements ServiceProviderInterface {

    public function register(Application $app)
    {
        $this->registerFieldTypeCollection($app);
        $this->registerDefaultFieldTypes($app);
    }

    protected function registerFieldTypeCollection(Application $app)
    {
        $app['fieldtypes'] = $app->share(function($app) {
            return new FieldTypeCollection;
        });
    }

    protected function registerDefaultFieldTypes(Application $app)
    {
        $defaultFields = new FieldTypeCollection(array(
            'text' => new TextFieldType($app),
            'image' => new ImageFieldType($app),
            'uploadcare' => new UploadcareFieldType($app),
            'slug' => new SlugFieldType($app),
            'html' => new HtmlFieldType($app),
            'video' => new VideoFieldType($app),
            'templateselect' => new TemplateSelectFieldType($app),
            'geolocation' => new GeolocationFieldType($app),
            'imagelist' => new ImageListFieldType($app),
            'file' => new FileFieldType($app),
            'filelist' => new FileListFieldType($app),
            'checkbox' => new CheckboxFieldType($app),
            'markdown' => new MarkdownFieldType($app),
            'datetime' => new DatetimeFieldType($app),
            'date' => new DateFieldType($app),
            'integer' => new IntegerFieldType($app),
            'float' => new FloatFieldType($app),
            'select' => new SelectFieldType($app),
            'textarea' => new TextareaFieldType($app),
        ));

        $app['fieldtypes'] = $app['fieldtypes']->merge($defaultFields);
    }

    public function boot(Application $app)
    {
    }

}
