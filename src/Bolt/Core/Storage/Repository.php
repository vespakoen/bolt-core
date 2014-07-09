<?php

namespace Bolt\Core\Storage;

class Repository {

    /**
     * @return \Bolt\Core\Config\Object\Collection\ContentCollection
     */
    public function all($loadRelated = true)
    {
        return $this->get(array(), $loadRelated);
    }

    public function find($id, $loadRelated = true)
    {
        $wheres = array($this->contentType->getKey() . '.id' => $id);

        return $this->findBy($wheres, $loadRelated);
    }

    public function findMany($ids)
    {
        return $this->get(array($this->contentType->getKey() . '.id' => $ids), false);
    }

    public function findBy($wheres, $loadRelated = true)
    {
        return $this->get($wheres, $loadRelated)
            ->first();
    }

}
