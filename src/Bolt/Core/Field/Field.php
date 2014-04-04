<?php

namespace Bolt\Core\Field;

use InvalidArgumentException;

use Bolt\Core\App;
use Bolt\Core\FieldType\FieldType;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field implements ArrayableInterface {

    protected $key;

    protected $type;

    protected $label;

    public function __construct($key, FieldType $type = null, $label = null)
    {
        $this->key = $key;
        $this->type = is_null($type) ? $this->getDefaultType() : $type;
        $this->label = is_null($label) ? $this->guessLabel() : $label;
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $type = App::make('fieldtypes')->get($config['type']);
        $label = array_get($config, 'label');

        return new static($key, $type, $label);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            throw new InvalidArgumentException(sprintf('Invalid field key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }

        $registeredFieldTypes = App::make('fieldtypes')->keys();
        if( ! array_key_exists('type', $config)) {
            throw new InvalidArgumentException(sprintf('Missing "type" key in field options for "%s". It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $key));
        }

        if(!in_array($config['type'], $registeredFieldTypes)) {
            throw new InvalidArgumentException(sprintf('Invalid "type" key (%s) in field options for "%s" field. It must be one of the following: '.implode(', ', $registeredFieldTypes).'.', $config['type'], $key));
        }

        if(in_array($key, static::getReservedFieldNames())) {
            throw new InvalidArgumentException(sprintf('Invalid key for Field "%s". It may NOT be named as the following reserved field names '.implode(',', $registeredFieldTypes).'.', $key));
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

    public function getLabel()
    {
        return $this->label;
    }

    public function toArray()
    {
        return array(
            'key' => $this->key,
            'type' => $this->type->getKey(),
            'label' => $this->label
        );
    }

    protected function getDefaultType()
    {
        return App::make('fieldtypes')->get('text');
    }

    protected function guessLabel()
    {
        return ucfirst(str_replace('_', ' ', $this->key));
    }

}
