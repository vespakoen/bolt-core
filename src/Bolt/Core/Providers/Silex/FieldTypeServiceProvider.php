<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\FieldType\FieldTypeCollection;
use Bolt\Core\FieldType\TextFieldType;
use Bolt\Core\FieldType\FieldType;

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
            'image' => FieldType::fromConfig('image'),
            'uploadcare' => FieldType::fromConfig('uploadcare'),
            'slug' => FieldType::fromConfig('slug'),
            'html' => FieldType::fromConfig('html'),
            'video' => FieldType::fromConfig('video'),
            'templateselect' => FieldType::fromConfig('templateselect'),
            'geolocation' => FieldType::fromConfig('geolocation'),
            'imagelist' => FieldType::fromConfig('imagelist'),
            'file' => FieldType::fromConfig('file'),
            'filelist' => FieldType::fromConfig('filelist'),
            'checkbox' => FieldType::fromConfig('checkbox'),
            'markdown' => FieldType::fromConfig('markdown'),
            'datetime' => FieldType::fromConfig('datetime'),
            'date' => FieldType::fromConfig('date'),
            'integer' => FieldType::fromConfig('integer'),
            'float' => FieldType::fromConfig('float'),
            'select' => FieldType::fromConfig('select'),
            'textarea' => FieldType::fromConfig('textarea', array(
                'column' => 'text',
                'doctrine_type' => 'Doctrine\DBAL\Types\TextType'
            )),
        ));

        $app['fieldtypes'] = $app['fieldtypes']->merge($defaultFields);
    }

    public function boot(Application $app)
    {
    }

}
