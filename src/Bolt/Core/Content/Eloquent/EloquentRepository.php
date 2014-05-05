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

	    $selects = array('*');

	    $fields = $this->contentType->getFields();
	    foreach ($fields as $field) {
	    	switch ($field->getType()->getType()) {
	    		case 'linestring':
	    		case 'point';
	    			$selects[] = new \Illuminate\Database\Query\Expression("ST_AsGeoJson(".$field->getKey().") as ".$field->getKey());
	    			break;
	    	}
	    }

	    $data = $model->select($selects)
	    	->get()
	    	->toArray();

	    return $this->app['contents.factory']->create($data);
	}

	/**
	 * @return bool
	 */
	public function store($attributes)
	{
	    return (bool) $this->getModel()
	    	->create($attributes);
	}

	/**
	 * @return Illuminate\Database\Eloquent\Model
	 */
	protected function getModel()
	{
	    return $this->app['model.' . $this->contentType->getKey()];
	}

}
