<?php

namespace Bolt\Core\Extension;

use Bolt\Core\Support\Collection;

class ExtensionCollection extends Collection {

    public static function fromConfig($config)
    {
        $collection = new static;

        foreach($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addExtension($key, Extension $contentType)
    {
        $this->items[$key] = $contentType;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = Extension::fromConfig($key, $config);

        return $this;
    }

}
