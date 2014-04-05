<?php

namespace Bolt\Core\Field;

use InvalidArgumentException;

use Bolt\Core\Support\Collection;

class FieldCollection extends Collection {

    protected $items = array();

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public static function fromConfig($config)
    {
        static::validate($config);

        $collection = new static;

        foreach($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addField($key, Field $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = Field::fromConfig($key, $config);

        return $this;
    }

    public function addColumnsTo($table) {
        foreach($this as $field) {
            $field->addColumnTo($table);
        }
    }

    public static function validate($config)
    {
        if(!is_array($config)) {
            throw new InvalidArgumentException(sprintf('Invalid "fields" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

}
