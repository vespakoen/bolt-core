<?php

namespace Bolt\Core\Config\Object\Collection;

use Bolt\Core\App;
use Bolt\Core\Config\Object\Relation;

class ExtensionCollection extends Collection
{
    public static function fromConfig($config)
    {
        $collection = new static;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addExtension($key, Extension $extension)
    {
        $this->items[$key] = $extension;

        return $this;
    }

    public function add($key, $config)
    {
        $extensionFactory = App::make('extension.factory');

        $this->items[$key] = $extensionFactory->fromConfig($key, $config);

        return $this;
    }

}
