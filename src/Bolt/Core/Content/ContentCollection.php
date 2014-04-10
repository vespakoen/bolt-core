<?php

namespace Bolt\Core\Content;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\Content;

class ContentCollection extends Collection {

    public static function fromConfig($config)
    {
        $collection = new static;

        foreach($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addContent($key, $content)
    {
        $this->items[$key] = $content;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = Content::fromConfig($key, $config);

        return $this;
    }

}
