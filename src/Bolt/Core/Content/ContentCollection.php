<?php

namespace Bolt\Core\Content;

use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\Content;

class ContentCollection extends Collection
{
    public function addContent($key, Content $content)
    {
        $this->items[$key] = $content;

        return $this;
    }
}
