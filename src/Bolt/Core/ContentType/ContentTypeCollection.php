<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\Support\Collection;

class ContentTypeCollection extends Collection {

    public static function fromConfig($config)
    {
        $collection = new static;

        foreach($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addContentType($key, ContentType $contentType)
    {
        $this->items[$key] = $contentType;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = ContentType::fromConfig($key, $config);

        return $this;
    }

}
