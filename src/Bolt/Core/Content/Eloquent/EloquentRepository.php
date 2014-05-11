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

    public function get($filtered = true)
    {
        $model = $this->getModel();

        $selects = $this->getSelects();

        $recordsQuery = $model->select($selects)
            ->with(array('incoming', 'outgoing'));

        if ($filtered) {
            $recordsQuery->join('relations', $this->contentType->getTableName().'.id', '=', 'relations.to_id')
                ->where('relations.from_id', '=', $this->app['session']->get('project_id'));
        }

        $records = $recordsQuery->get()
            ->toArray();

        $records = $this->loadRelatedFor($records);

        return $this->app['contents.factory']->create($records, $this->contentType);
    }

    /**
     * @return \Bolt\Core\Content\ContentCollection
     */
    public function getForListing($sort, $order = 'asc', $offset = 0, $limit = 10, $search = null, $filtered = true)
    {
        $model = $this->getModel();

        if ($filtered) {
            $filtered = $this->contentType->getKey() !== $this->app['config']->get('app/project/contenttype');
        }

        $selects = $this->getSelects();

        $recordsQuery = $model
            ->with(array('incoming', 'outgoing'))
            ->select($selects);

        if ($filtered) {
            $recordsQuery->join('relations', $this->contentType->getTableName().'.id', '=', 'relations.to_id')
                ->where('relations.from_id', '=', $this->app['session']->get('project_id'));
        }

        if ( ! is_null($search)) {
            foreach ($this->contentType->getSearchFields()->getDatabaseFields() as $searchField) {
                $recordsQuery->orWhere(new Expression('LOWER(' . $searchField->getKey() . ')'), 'LIKE', '%' . strtolower($search) . '%');
            }
        }

        $records = $recordsQuery
            ->get()
            ->toArray();

        $records = $this->loadRelatedFor($records);

        return $this->app['contents.factory']->create($records, $this->contentType);
    }

    public function find($id)
    {
        $model = $this->getModel();

        $selects = $this->getSelects();

        $result = $model->with(array('incoming', 'outgoing'))
            ->select($selects)
            ->find($id);

        if ( ! $result) {
            return false;
        }

        $records = array($result->toArray());
        $records = $this->loadRelatedFor($records);
        $record = $records[0];

        return $this->app['content.factory']->create($record, $this->contentType);
    }

    public function findMany($ids)
    {
        $model = $this->getModel();

        $selects = $this->getSelects();

        $records = $model->select($selects)
            ->whereIn('id', $ids)
            ->get()
            ->toArray();

        $records = array_build($records, function($key, $record) {
            return array($record['id'], $record);
        });

        return $this->app['contents.factory']->create($records, $this->contentType);
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
            foreach($record['incoming'] as $other) {
                $contentTypeKey = $other['from_type'];
                $other = $related[$contentTypeKey]->get($other['from_id']);
                if ( ! $other) continue;
                $incoming[$contentTypeKey][$other->get('id')] = $other->get();
            }
            $record['incoming'] = array();
            foreach($incoming as $contentTypeKey => $attributes) {
                $record['incoming'][$contentTypeKey] = $this->app['contents.factory']->create($attributes, $this->app['contenttypes']->get($contentTypeKey));
            }

            $outgoing = array();
            foreach($record['outgoing'] as $other) {
                $contentTypeKey = $other['to_type'];
                $other = $related[$contentTypeKey]->get($other['to_id']);
                if ( ! $other) continue;
                $outgoing[$contentTypeKey][$other->get('id')] = $other->get();
            }
            $record['outgoing'] = array();
            foreach($outgoing as $contentTypeKey => $attributes) {
                $record['outgoing'][$contentTypeKey] = $this->app['contents.factory']->create($attributes, $this->app['contenttypes']->get($contentTypeKey));
            }
        }

        return $records;
    }

    /**
     * @return bool
     */
    public function store($attributes)
    {
        $result = $this->getModel()
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
        $model = $this->getModel()
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
        $model = $this->getModel()
            ->find($id);

        if ( ! $model) {
            return false;
        }

        return $model->delete();
    }

    public function updateRelations($fromId, $relations)
    {
        $fromType = $this->contentType->getKey();

        $relationModel = $this->app['model.eloquent.relations'];

        // remove current outgoing relations
        $relationModel
            ->where('from_type', '=', $fromType)
            ->where('from_id', '=', $fromId)
            ->delete();

        $newRelations = array();
        foreach ($relations as $toType => $toIds) {
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

        if(count($newRelations) > 0) {
            $relationModel->insert($newRelations);
        }
    }

    /**
     * @return Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        return $this->model;
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
