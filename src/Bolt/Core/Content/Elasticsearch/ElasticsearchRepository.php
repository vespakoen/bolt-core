<?php

namespace Bolt\Core\Content\Elasticsearch;

use Bolt\Core\ContentType\ContentType;
use Bolt\Core\Content\WriteRepositoryInterface;

use Illuminate\Database\Query\Expression;

use DateTime;

class ElasticsearchRepository implements WriteRepositoryInterface
{
    public function __construct($app, $client, ContentType $contentType)
    {
        $this->app = $app;
        $this->client = $client;
        $this->contentType = $contentType;
    }

    /**
     * @return bool
     */
    public function store($attributes)
    {
        $attributes = $this->serialize($attributes);

        $contentType = $this->contentType;
        if( ! $contentType->get('es_type', false)) return;

        $params = array(
            'body' => $attributes,
            'index' => $contentType->get('es_index', $this->app['session']->get('project_namespace')),
            'type' => $contentType->get('es_type'),
            'id' => $attributes['id'],
        );

        $this->client->index($params);

        return true;
    }

    /**
     * @return bool
     */
    public function update($id, $attributes)
    {
        $attributes = $this->serialize($attributes);

        $contentType = $this->contentType;
        if( ! $contentType->get('es_type', false)) return;

        $params = array(
            'body' => $attributes,
            'index' => $contentType->get('es_index', $this->app['session']->get('project_namespace')),
            'type' => $contentType->get('es_type'),
            'id' => $attributes['id'],
        );

        $this->client->index($params);

        return true;
    }

    public function delete($id)
    {
        $contentType = $this->contentType;
        if( ! $contentType->get('es_type', false)) return;

        $params = array(
            'index' => $contentType->get('es_index', $this->app['session']->get('project_namespace')),
            'type' => $contentType->get('es_type'),
            'id' => $id,
        );

        $this->client->delete($params);

        return true;
    }

    public function updateRelations($fromId, $relations)
    {
        // noop, we already synced the relations in the update, no need for a second request =)
    }

    protected function serialize($attributes)
    {
        // throw them id's together
        $links = array();
        if (array_key_exists('outgoing', $attributes)) {
            foreach ($attributes['outgoing'] as $type => $related) {
                if($type == $this->app['config']->get('app/project/contenttype')) continue;
                $links = array_merge($links, array_keys($related));
            }
        }

        $relations = $this->contentType->getRelations();
        if (array_key_exists('links', $attributes)) {
            foreach ($attributes['links'] as $type => $ids) {
                if($type == $this->app['config']->get('app/project/contenttype') || $relations->get($type)->get('inverted', false) == true) continue;
                $links = array_merge($links, $ids);
            }
        }
        unset($attributes['incoming']);
        unset($attributes['outgoing']);
        $attributes['links'] = $links;

        // new cutouts
        $fields = $this->contentType->getAllFields();
        foreach ($fields->filterByTypeKey('uploadcare') as $field) {
            foreach ($field->get('cutouts', array()) as $key => $dimensions) {
                $w = $dimensions['w'];
                $h = $dimensions['h'];
                $attributes[$field->getKey().'_'.$key] = empty($attributes[$field->getKey()]) ? '' : $attributes[$field->getKey()].'-/scale_crop/'.round($w).'x'.round($h).'/center/img.jpg';
            }
        }

        foreach ($attributes as $key => $value) {
            if (substr($key, 0, 4) == 'date') {
                $newKey = str_replace('date', 'date_', $key);

                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $attributes[$newKey] = $value;
                unset($attributes[$key]);
            }

            if ($field = $fields->get($key)) {
                $type = $field->getType()->getKey();
                if ($type == 'linestring') {
                    $attributes[$key] = json_decode($value);
                }

                if ($type == 'point') {
                    $data = json_decode($value);
                    $attributes[$key] = isset($data->coordinates) ? $data->coordinates : array(11.2, 23.4);
                }
            }
        }

        ksort($attributes);

        return $attributes;
    }

}
