<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\ContentType\ContentType;
use Bolt\Core\Content\ReadRepositoryInterface;
use Bolt\Core\Content\WriteRepositoryInterface;

use Illuminate\Database\Query\Expression;

class MongoDBRepository extends Repository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    public function __construct($app, ContentType $contentType)
    {
        $this->app = $app;
        $this->contentType = $contentType;
    }

    /**
     * @return \Bolt\Core\Content\ContentCollection
     */
    public function get($wheres = array(), $loadRelated = true, $sort = null, $order = 'asc', $offset = null, $limit = null, $search = null)
    {
        return $this->app['contents.factory']->create($records, $this->contentType);
    }

    /**
     * @return \Bolt\Core\Content\ContentCollection
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

    public function findBy($wheres, $loadRelated = true)
    {
        return $this->get($wheres, $loadRelated)
            ->first();
    }

    public function count()
    {
        $model = $this->model;

        return $model->count();
    }

    /**
     * @return bool
     */
    public function store($attributes)
    {

    }

    /**
     * @return bool
     */
    public function update($id, $attributes)
    {

    }

    public function delete($id)
    {

    }

    protected function findMany($ids)
    {
        return $this->get(array($this->contentType->getKey() . '.id' => $ids), false);
    }

}
