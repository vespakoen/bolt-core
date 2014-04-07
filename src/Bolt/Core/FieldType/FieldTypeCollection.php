<?php

namespace Bolt\Core\FieldType;

use InvalidArgumentException;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;

class FieldTypeCollection extends Collection {

    public static function fromConfig($config)
    {
        static::validate($config);

        $collection = new static;

        foreach($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addFieldType($key, FieldType $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = FieldType::fromConfig($key, $config);

        return $this;
    }

    public static function validate($config)
    {
        $app = App::instance();

        if(!is_array($config)) {
            $app['notify']->error(sprintf('Invalid "fieldtypes" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

}
