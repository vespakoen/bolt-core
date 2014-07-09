<?php

namespace Bolt\Core\Config\Object\Collection;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Config\Object\FieldType;

class FieldTypeCollection extends Collection
{
    public function addFieldType($key, FieldType $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $fieldTypeFactory = App::make('fieldtype.factory');

        $this->items[$key] = $fieldTypeFactory->fromConfig($key, $config);

        return $this;
    }

}
