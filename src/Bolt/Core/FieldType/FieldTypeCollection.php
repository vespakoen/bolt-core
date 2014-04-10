<?php

namespace Bolt\Core\FieldType;

use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\FieldType;

class FieldTypeCollection extends Collection
{
    public function addFieldType($key, $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = FieldType::fromConfig($key, $config);

        return $this;
    }

}
