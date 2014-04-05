<?php

namespace Bolt\Core\Field;

use InvalidArgumentException;

use Bolt\Core\App;
use Bolt\Core\FieldType\FieldType;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field implements ArrayableInterface {

    protected $key;

    protected $type;

    protected $options;

    public function __construct($key, FieldType $type = null, $options = array())
    {
        $this->key = $key;
        $this->type = is_null($type) ? $this->getDefaultType() : $type;
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $type = App::make('fieldtypes')->get($config['type']);
        $options = array_except($config, array('type'));

        return new static($key, $type, $options);
    }

    public static function validate($key, $config)
    {
        $app = App::instance();

        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            $app['notify']->error(sprintf('Invalid field key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }

        $registeredFieldTypes = $app['fieldtypes']->keys();
        if( ! array_key_exists('type', $config)) {
            $app['notify']->error(sprintf('Missing "type" key in field options for "%s". It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $key));
        }

        if(!in_array($config['type'], $registeredFieldTypes)) {
            $app['notify']->error(sprintf('Invalid "type" key (%s) in field options for "%s" field. It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $config['type'], $key));
        }

        if($config['type'] != 'slug' && in_array($key, static::getReservedFieldNames())) {
            $app['notify']->error(sprintf('Invalid key for Field "%s". It may NOT be named as the following reserved field names '.implode(',', static::getReservedFieldNames()).'.', $key));
        }
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

    public function getOptions()
    {
        return $this->label;
    }

    public function toArray()
    {
        return array_merge(array(
            'key' => $this->key,
            'type' => $this->type->getKey()
        ), $this->options);
    }

    protected function getDefaultType()
    {
        return App::make('fieldtypes')->get('text');
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
