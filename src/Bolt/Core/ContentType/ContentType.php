<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\Field\FieldCollection;

use Illuminate\Support\Contracts\ArrayableInterface;

class ContentType implements ArrayableInterface {

    public function __construct($key, $name, $singularName = null, FieldCollection $fields = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->singularName = $singularName;
        $this->fields = $fields;
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $name = $config['name'];
        $singularName = $config['singular_name'];
        $collection = FieldCollection::fromConfig($config['fields']);

        return new static($key, $name, $singularName, $collection);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            throw new InvalidArgumentException(sprintf('Invalid ContentType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }
    }

    public function toArray()
    {
        return array(
            'key' => $this->key,
            'name' => $this->name,
            'singular_name' => $this->singularName,
            'fields' => $this->fields->toArray()
        );
    }

}
