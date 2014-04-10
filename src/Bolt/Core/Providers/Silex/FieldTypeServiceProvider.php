<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\FieldType\Factory\FieldType;
use Bolt\Core\FieldType\Factory\FieldTypeCollection;

use Bolt\Core\FieldType\StringFieldType;
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

class FieldTypeServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerFieldTypeFactories($app);
        $this->registerFieldTypeCollection($app);
        $this->registerDefaultFieldTypes($app);
    }

    protected function registerFieldTypeFactories(Application $app)
    {
        $app['fieldtype.factory'] = $app->share(function ($app) {
            return new FieldType($app);
        });

        $app['fieldtypes.factory'] = $app->share(function ($app) {
            return new FieldTypeCollection($app);
        });
    }

    protected function registerFieldTypeCollection($app)
    {
        $app['fieldtypes'] = $app->share(function ($app) {
            return $app['fieldtypes.factory']->create();
        });
    }

    protected function registerDefaultFieldTypes(Application $app)
    {
        $app['fieldtypes']
            ->addFieldType('string', new StringFieldType($app))
            ->addFieldType('image', new ImageFieldType($app))
            ->addFieldType('uploadcare', new UploadcareFieldType($app))
            ->addFieldType('slug', new SlugFieldType($app))
            ->addFieldType('html', new HtmlFieldType($app))
            ->addFieldType('video', new VideoFieldType($app))
            ->addFieldType('templateselect', new TemplateSelectFieldType($app))
            ->addFieldType('geolocation', new GeolocationFieldType($app))
            ->addFieldType('imagelist', new ImageListFieldType($app))
            ->addFieldType('file', new FileFieldType($app))
            ->addFieldType('filelist', new FileListFieldType($app))
            ->addFieldType('checkbox', new CheckboxFieldType($app))
            ->addFieldType('markdown', new MarkdownFieldType($app))
            ->addFieldType('datetime', new DatetimeFieldType($app))
            ->addFieldType('date', new DateFieldType($app))
            ->addFieldType('integer', new IntegerFieldType($app))
            ->addFieldType('float', new FloatFieldType($app))
            ->addFieldType('select', new SelectFieldType($app))
            ->addFieldType('textarea', new TextareaFieldType($app));
    }

    public function boot(Application $app)
    {
    }

}
