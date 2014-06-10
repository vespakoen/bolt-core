<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\ContentType\ContentType;
use Bolt\Core\Content\ReadRepositoryInterface;
use Bolt\Core\Content\WriteRepositoryInterface;

use Illuminate\Database\Query\Expression;

class EloquentRepository implements ReadRepositoryInterface, WriteRepositoryInterface
{
    public function __construct($app, $model, ContentType $contentType)
    {
        $this->app = $app;
        $this->model = $model;
        $this->contentType = $contentType;
    }

    /**
     * @return \Bolt\Core\Content\ContentCollection
     */
    public function get($wheres = array(), $loadRelated = true, $sort = null, $order = 'asc', $offset = null, $limit = null, $search = null)
    {
        $selects = $this->getSelects();

        $recordsQuery = $this->model;

        if($loadRelated) {
            $recordsQuery = $recordsQuery->with(array('incoming', 'outgoing'));
        }

        $recordsQuery = $recordsQuery->select($selects);
        foreach ($wheres as $key => $value) {
            if (is_array($value) && count($value) > 0) {
                $recordsQuery = $recordsQuery->whereIn($key, $value);
            } else {
                $recordsQuery = $recordsQuery->where($key, '=', $value);
            }
        }

        if ( ! is_null($offset)) {
            $recordsQuery = $recordsQuery->skip($offset);
        }

        if ( ! is_null($limit)) {
            $recordsQuery = $recordsQuery->take($limit);
        }

        foreach ($this->getRelationTableJoin($wheres) as $join) {
            $recordsQuery->join($join['table'], $join['left'], '=', $join['right']);
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

        if( ! is_null($sort)) {
            $recordsQuery = $recordsQuery->orderBy($sort, $order);
        }

        $records = $recordsQuery
            ->get()
            ->toArray();

        $records = array_build($records, function($key, $record) {
            return array($record['id'], $record);
        });

        if ($loadRelated) {
            $records = $this->loadRelatedFor($records);
        }

        return $this->app['contents.factory']->create($records, $this->contentType);
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

    /**
     * @return \Bolt\Core\Content\ContentCollection
     */
    public function all($loadRelated = true)
    {
        return $this->get(array(), $loadRelated, null, null, null, null);
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
    public function store($attributes)
    {
        if( ! array_key_exists('id', $attributes)) {
            $attributes['id'] = $this->uuid();
        }

        $result = $this->model
            ->create($attributes);

        if ($result) {
            return $result->toArray();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function update($id, $attributes)
    {
        $model = $this->model
            ->find($id);

        if ($model) {
            $model->fill($attributes);
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

    public function updateRelations($fromId, $relationData)
    {
        $relations = $this->contentType->getRelations();
        $fromType = $this->contentType->getKey();

        $relationModel = $this->app['model.eloquent.relations'];

        // remove current outgoing relations
        $relationModel
            ->where('from_type', '=', $fromType)
            ->where('from_id', '=', $fromId)
            ->delete();

        $newRelations = array();
        foreach ($relationData as $toType => $toIds) {
            $relation = $relations->get($toType);
            if ($relation->get('inverted', false)) {
                $relationModel
                    ->where('to_type', '=', $fromType)
                    ->where('to_id', '=', $fromId)
                    ->delete();

                foreach ($toIds as $toId) {
                    $newRelations[] = array(
                        'id' => $this->uuid(),
                        'to_type' => $fromType,
                        'to_id' => $fromId,
                        'from_type' => $toType,
                        'from_id' => $toId
                    );
                }
            } else {
                foreach ($toIds as $toId) {
                    $newRelations[] = array(
                        'id' => $this->uuid(),
                        'from_type' => $fromType,
                        'from_id' => $fromId,
                        'to_type' => $toType,
                        'to_id' => $toId
                    );
                }
            }
        }

        if(count($newRelations) > 0) {
            $relationModel->insert($newRelations);
        }
    }

    protected function findMany($ids)
    {
        return $this->get(array($this->contentType->getKey() . '.id' => $ids), false);
    }

    protected function getSelects()
    {
        $selects = array($this->contentType->getKey().'.*');

        $fields = $this->contentType->getFields();
        foreach ($fields as $field) {
            switch ($field->getType()->getType()) {
                case 'linestring':
                case 'point';
                    $selects[] = new Expression("ST_AsGeoJson(".$field->getKey().") as ".$field->getKey());
                    break;
            }
        }

        return $selects;
    }

    protected function uuid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
