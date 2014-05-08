<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\Content\AbstractRepository;
use Illuminate\Database\Query\Expression;

class EloquentRepository extends AbstractRepository
{
    /**
     * @return \Bolt\Core\Content\ContentCollection
     */
    public function getForListing($sort, $order = 'asc', $offset = 0, $limit = 10, $search = null)
    {
        $model = $this->getModel();

        $selects = array('*');

        $fields = $this->contentType->getFields();
        foreach ($fields as $field) {
            switch ($field->getType()->getType()) {
                case 'linestring':
                case 'point';
                    $selects[] = new Expression("ST_AsGeoJson(".$field->getKey().") as ".$field->getKey());
                    break;
            }
        }

        $recordsQuery = $model
         //   ->with(array('links', 'links.other'))
            ->select($selects);

        if ( ! is_null($search)) {
            foreach ($this->contentType->getSearchFields()->getDatabaseFields() as $searchField) {
                $recordsQuery->orWhere(new Expression('LOWER(' . $searchField->getKey() . ')'), 'LIKE', '%' . strtolower($search) . '%');
            }
        }

        $records = $recordsQuery
            ->get()
            ->toArray();

        $results = array();
        foreach ($records as $record) {
            // $record['links'] = array_pluck($record['links'], 'other');
            $results[] = $record;
        }

        return $this->app['contents.factory']->create($results, $this->contentType);
    }

    public function find($id)
    {
        $model = $this->getModel();

        $record = $model//->with('links')
            ->find($id)
            ->toArray();

        //$record['links'] = array_pluck($record['links'], 'other');

        return $this->app['content.factory']->create($record, $this->contentType);
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
