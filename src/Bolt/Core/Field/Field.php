<?php

namespace Bolt\Core\Field;

use Bolt\Core\App;
use Bolt\Core\FieldType\FieldType;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field extends ConfigObject implements ArrayableInterface {

    protected $objectType = 'field';

    protected $key;

    protected $type;

    protected $options;

    public function __construct($app, $key, FieldType $type = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->type = is_null($type) ? $this->getDefaultType() : $type;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
    }

    public static function fromConfig($key, $config)
    {
        static::validateConfig($key, $config);

        $app = App::instance();
        $type = $app['fieldtypes']->get($config['type']);
        $options = array_except($config, array('type'));

        return new static($app, $key, $type, $options);
    }

    public static function getReservedFieldNames()
    {
        return array(
            'id',
            'slug',
            'datecreated',
            'datechanged',
            'datepublish',
            'datedepublish',
            'ownerid',
            'username',
            'status',
            'link'
        );
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getType()
    {
        return $this->type;
    }

    public static function validateConfig($key, $config)
    {
        $app = App::instance();

        if( ! array_key_exists('type', $config)) {
            $app['notify']->error(sprintf('Not type given for field with key: "%s"', $key));
        }

        $registeredFieldTypes = $app['fieldtypes']->keys();
        if(!in_array($config['type'], $registeredFieldTypes)) {
            $app['notify']->error(sprintf('Invalid "type" key (%s) in field options for "%s" field. It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', get_class($this->type), $this->key));
        }
    }

    public function validate()
    {
        $app = $this->app;

        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if($this->key !== $cleaned) {
            $app['notify']->error(sprintf('Invalid field key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }

        if($this->type->getKey() !== 'slug' && in_array($this->key, static::getReservedFieldNames())) {
            $app['notify']->error(sprintf('Invalid key for Field "%s". It may NOT be named as the following reserved field names '.implode(',', static::getReservedFieldNames()).'.', $this->key));
        }
    }

    protected function getDefaultType()
    {
        return $this->app['fieldtypes']->get('text');
    }

    protected function getDefaultOptions()
    {
        return array(
            'label' => $this->guessLabel(),
            'class' => '',
            'variant' => '',
            'default' => '',
            'pattern' => '',
        );
    }

    protected function guessLabel()
    {
        return ucfirst(str_replace('_', ' ', $this->key));
    }

}
