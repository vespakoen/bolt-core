<?php

namespace Bolt\Core\Config\Object\Collection;

use InvalidArgumentException;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Config\Object\Relation;

class RelationCollection extends Collection
{
    protected $items = array();

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public function addRelation($key, Relation $relation)
    {
        $this->items[$key] = $relation;

        return $this;
    }

    public function add($key, $config)
    {
        $relationFactory = App::make('relation.factory');

        $this->items[$key] = $relationFactory->fromConfig($key, $config);

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
