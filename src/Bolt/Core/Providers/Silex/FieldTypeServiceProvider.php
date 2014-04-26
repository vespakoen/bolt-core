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

    protected function registerDefaultFieldTypes($app)
    {
        $app['fieldtypes'] = $app->share(function($app) {
            $config = $app['config']->get('fieldtypes');
            return $app['fieldtypes.factory']->fromConfig($config);
        });
    }

    public function boot(Application $app)
    {
    }

}
