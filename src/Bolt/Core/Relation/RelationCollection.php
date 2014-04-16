<?php

namespace Bolt\Core\Relation;

use InvalidArgumentException;

use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\Relation;

class RelationCollection extends Collection
{
    protected $items = array();

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public function addRelation($key, $relation)
    {
        $this->items[$key] = $relation;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = Relation::fromConfig($key, $config);

        return $this;
    }

    public function addColumnsTo($table)
    {
        foreach ($this as $relation) {
            $relation->addColumnsTo($table);
        }
    }

    public static function validate($config)
    {
        if (!is_array($config)) {
            throw new InvalidArgumentException(sprintf('Invalid "relations" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

}
