<?php

namespace Bolt\Core\Config\Object\Collection;

use Bolt\Core\Support\Collection;
use Bolt\Core\Config\Object\Content;

class ContentCollection extends Collection
{
    public function addContent($key, Content $content)
    {
        $this->items[$key] = $content;

        return $this;
    }

    public function filterByAttribute($key, $value)
    {
        return $this->filter(function($content) use ($key, $value) {
            return $content->get($key) == $value;
        });
    }
}