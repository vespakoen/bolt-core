<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\Content\AbstractRepository;

class EloquentRepository extends AbstractRepository
{
	/**
	 * @return \Bolt\Core\Content\ContentCollection
	 */
	public function getForListing($sort, $order, $search, $offset, $limit)
	{
	    $model = $this->getModel();

	    return $this->app['contents.factory']->create($model->get()->toArray());
	}

	/**
	 * @return bool
	 */
	public function store($attributes)
	{
	    return (bool) $this->getModel()->create($attributes);
	}

	/**
	 * @return Illuminate\Database\Eloquent\Model
	 */
	protected function getModel()
	{
	    return $this->app['model.' . $this->contentType->getKey()];
	}

}
