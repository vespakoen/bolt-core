<?php

namespace Bolt\Core\Config\Object\Collection;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Paginator;
use Bolt\Core\Config\Object\Content;

class ContentCollection extends Collection
{
    /**
     * Create a new collection.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = array(), $total = null)
    {
        $this->items = $items;
        $this->total = $total;
    }

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

    public function getPaginator()
    {
        $app = App::instance();

        $request = $app['request'];

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        return new Paginator($request, $page, $limit, $this->total);
    }
}
