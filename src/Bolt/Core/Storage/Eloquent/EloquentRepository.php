<?php

namespace Bolt\Core\Storage\Eloquent;

use Bolt\Core\Storage\Repository;
use Bolt\Core\Config\Object\ContentType;
use Bolt\Core\Config\Object\Collection\ContentCollection;
use Bolt\Core\Storage\ReadRepositoryInterface;
use Bolt\Core\Storage\WriteRepositoryInterface;

use Illuminate\Database\Query\Expression;

class EloquentRepository extends Repository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    public function __construct($app, $model, ContentType $contentType)
    {
        $this->app = $app;
        $this->model = $model;
        $this->contentType = $contentType;
    }

    /**
     * @return \Bolt\Core\Storage\ContentCollection
     */
    public function get($wheres = array(), $loadRelated = true, $sort = null, $order = 'asc', $offset = null, $limit = null, $search = null)
    {
        $selects = $this->getSelects();

        $recordsQuery = $this->model;

        if($loadRelated) {
            $recordsQuery = $recordsQuery->with(array('incoming', 'outgoing'));
        }

        foreach ($this->getRelationTableJoin($wheres) as $join) {
            $recordsQuery = $recordsQuery->join($join['table'], $join['left'], '=', $join['right']);
        }

        $recordsQuery = $recordsQuery->select($selects);
        foreach ($wheres as $key => $value) {
            if (is_array($value) && count($value) > 0) {
                $recordsQuery = $recordsQuery->whereIn($key, $value);
            } else {
                $recordsQuery = $recordsQuery->where($key, '=', $value);
            }
        }

        if ( ! is_null($search)) {
            $searchFields = $this->contentType
                ->getSearchFields()
                ->getDatabaseFields();

            $recordsQuery->where(function($query) use ($searchFields, $search) {
                foreach ($searchFields as $searchField) {
                    $query->orWhere(new Expression('LOWER(' . $searchField->getKey() . ')'), 'LIKE', '%' . strtolower($search) . '%');
                }
            });
        }

        $total = $recordsQuery->count();

        if ( ! is_null($offset)) {
            $recordsQuery = $recordsQuery->skip($offset);
        }

        if ( ! is_null($limit)) {
            $recordsQuery = $recordsQuery->take($limit);
        }

        if( ! is_null($sort)) {
            $recordsQuery = $recordsQuery->orderBy($sort, $order);
        }

        $records = $recordsQuery
            ->get()
            ->toArray();

        $me = $this;
        $records = array_build($records, function($key, $record) use ($me) {
            $record = $me->callGetters($record);

            return array($record['id'], $record);
        });

        if ($loadRelated) {
            $records = $this->loadRelatedFor($records);
        }

        return $this->app['contents.factory']->create($records, $this->contentType, $total);
    }

    /**
     * This method checks for the presence of 'incoming' or 'outgoing'
     * table names in the provided where clauses and returns an array
     * of joins that should be made
     *
     * @param  array $wheres  An array of where clauses
     * @return array          An array of joins that need to be made for the where clauses
     */
    protected function getRelationTableJoin($wheres)
    {
        $joins = array();
        foreach($wheres as $tableAndColumn => $value) {
            // split the table and the column name
            list($table, $column) = explode('.', $tableAndColumn);

            // join relations (AS "incoming") on from_id if the table name is incoming
            if ($table == 'incoming') {
                $joins[] = array(
                    'table' => 'relations AS incoming',
                    'left' => $this->contentType->getTableName().'.id',
                    'right' => 'incoming.from_id'
                );
            }

            // join relations (AS "outgoing") on to_id if the table name is outgoing
            if ($table == 'outgoing') {
                $joins[] = array(
                    'table' => 'relations AS outgoing',
                    'left' => $this->contentType->getTableName().'.id',
                    'right' => 'outgoing.to_id'
                );
            }
        }

        return $joins;
    }

    public function count()
    {
        $model = $this->model;

        return $model->count();
    }

    public function loadRelatedFor($records) {
        // collect related stuff by type
        $collected = array();
        foreach ($records as $record) {
            foreach ($record['incoming'] as $incoming) {
                $contentTypeKey = $incoming['from_type'];
                $collected[$contentTypeKey][$incoming['from_id']] = null;
            }

            foreach ($record['outgoing'] as $outgoing) {
                $contentTypeKey = $outgoing['to_type'];
                $collected[$contentTypeKey][$outgoing['to_id']] = null;
            }
        }

        // load all related data
        foreach ($collected as $contentTypeKey => $ids) {
            $ids = array_keys($ids);
            $results = $this->app['repository.eloquent.' . $contentTypeKey]->findMany($ids);
            $related[$contentTypeKey] = $results;
        }

        // glue it back together
        foreach ($records as &$record) {
            $incoming = array();
            foreach($record['incoming'] as $relation) {
                $contentTypeKey = $relation['from_type'];
                $other = $related[$contentTypeKey]->get($relation['from_id']);
                if ( ! $other) continue;
                $incoming[$contentTypeKey][$other->get('id')] = $other->get();
            }
            $record['incoming'] = array();
            foreach($incoming as $contentTypeKey => $contents) {
                $record['incoming'][$contentTypeKey] = $this->app['contents.factory']->create($contents, $this->app['contenttypes']->get($contentTypeKey));
            }

            $outgoing = array();
            foreach($record['outgoing'] as $relation) {
                $contentTypeKey = $relation['to_type'];
                $other = $related[$contentTypeKey]->get($relation['to_id']);
                if ( ! $other) continue;
                $outgoing[$contentTypeKey][$other->get('id')] = $other->get();
            }

            $record['outgoing'] = array();
            foreach($outgoing as $contentTypeKey => $contents) {
                $record['outgoing'][$contentTypeKey] = $this->app['contents.factory']->create($contents, $this->app['contenttypes']->get($contentTypeKey));
            }
        }

        return $records;
    }

    /**
     * @return bool
     */
    public function store($input)
    {
        if( ! array_key_exists('id', $input)) {
            $input['id'] = $this->uuid();
        }

        $input = $this->callSetters($input);
        $result = $this->model
            ->create($input);

        if ($result) {
            return $result->toArray();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update($id, $input)
    {
        $model = $this->model
            ->find($id);

        if ($model) {
            $input = $this->callSetters($input);
            $model->fill($input);
            $result = $model->save();

            if ($result) {
                return $model->toArray();
            }
        }

        return false;
    }

    public function delete($id)
    {
        $model = $this->model
            ->find($id);

        if ( ! $model) {
            return false;
        }

        return $model->delete();
    }

    // @todo optimize
    public function deleteMany($ids)
    {
        foreach ($ids as $id) {
            $this->delete($id);
        }

        return true;
    }

    public function reorder($id, $from, $to)
    {
        // $repository->where('weiefght', '>', 0)
        //     ->update(array('weigefht' => new \Illuminate\Database\Query\Expression('wefight + 1')));

        // $weightField = $contentType->getDefaultFields()->forPurpose('weight');
        // $direction = $request->get('direction');
        // $currentItem = $this->model->find($id);

        // // ignore cases we don't like
        // if (
        //     ! $weightField || // no weight field specified @todo throw an error
        //     ! $currentItem // no item found that has to be moved
        // ) {
        //     return $this->back();
        // }

        // $weightFieldKey = $weightField->getKey();
        // $currentWeight = $currentItem->weight;

        // if ($currentWeight == 0) {
        //     // table is fucked, we will clean up the weigths, and let the user try again
        //     $contents = $model->get();
        //     foreach ($contents as $i => $content) {
        //         $content->weight = $i + 1;
        //         $content->save();
        //     }

        //     // sync elasticsearch index
        // }

        // if ($direction == 'down') {
        //     // move next item up
        //     // move current item down

        //     $contents = $model->where('weight', '<', $currentWeight)
        //         ->orderBy($weightFieldKey, 'asc')
        //         ->get();
        // } else {
        //     // move previous item down
        //     // move current item up

        //     $contents = $model->where('weight', '<', $currentWeight)
        //         ->orderBy($weightFieldKey, 'asc')
        //         ->get();
        // }
    }

    protected function callSetters($attributes)
    {
        $result = array();

        $fields = $this->contentType->getDatabaseFields();
        foreach ($attributes as $key => $value) {
            if ($field = $fields->get($key)) {
                $result[$key] = $field->setter($value, $this);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function callGetters($attributes)
    {
        $result = array();
        $fields = $this->contentType->getDatabaseFields();
        foreach ($attributes as $key => $value) {
            if ($field = $fields->get($key)) {
                $result[$key] = $field->getter($value, $this);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    protected function getSelects()
    {
        $selects = array();

        $fields = $this->contentType->getDatabaseFields();
        foreach ($fields as $field) {
            $selects[] = $field->selector($this->contentType->getTableName());
        }

        return $selects;
    }

    protected function uuid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return strtolower(trim(com_create_guid(), '{}'));
        }

        return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
    }
}
