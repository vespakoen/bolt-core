<?php

namespace Bolt\Core\Content\Elasticsearch;

class ElasticsearchManager
{
    public function __construct($app, $client)
    {
        $this->app = $app;
        $this->client = $client;
    }

    public function dropIndex($namespace)
    {
        // Drop index (if exists)
        $deleteParams['index'] = $namespace;

        try {
            $this->client->indices()->delete($deleteParams);
        } catch (\Exception $e) {
            // we don't care
            echo $e->getMessage();
        }
    }

    public function createIndex($namespace)
    {
        // Create index
        $indexParams = array(
            'index' => $namespace,
            'body' => array(
                'mappings' => array()
            )
        );

        foreach ($this->app['contenttypes'] as $contentType) {
            if($contentType->get('es_type', false)) {
                $mappings = array(
                    'links' => array(
                        "type" => "string",
                        "index" => "not_analyzed",
                    )
                );

                foreach ($contentType->getFields()->merge($contentType->getDefaultFields()) as $field) {
                    $type = $field->getType()->getKey();

                    if ($field->getKey() == 'id') {
                        $mappings[$field->getKey()] = array(
                            "type" => "string",
                            "index" => "not_analyzed"
                        );
                    }

                    if ($type == 'point') {
                        $mappings[$field->getKey()] = array(
                            "type" => "geo_point"
                        );
                    }

                    if ($type == 'linestring' || $type == 'area') {
                        $mappings[$field->getKey()] = array(
                            "type" => "geo_shape",
                            "tree" => "quadtree",
                            "precision" => "10m"
                        );
                    }

                    if ($type == 'datetime') {
                        $key = $field->getKey();
                        $key = str_replace('date', 'date_', $key);

                        $mappings[$key] = array(
                            "type" => "date",
                            "format" => "yyyy-MM-dd HH:mm:ss"
                        );
                    }

                    if ($field->getKey() == 'namespace' || $type == 'guid') {
                        $mappings[$field->getKey()] = array(
                            "boost" => 1.0,
                            "index" => "not_analyzed",
                            "store" => "yes",
                            "type" => "string"
                        );
                    }
                }

                if($contentType->get('es_type')) {
                    $indexParams['body']['mappings'][$contentType->get('es_type')]['properties'] = $mappings;
                }
            }
        }

        $this->client->indices()->create($indexParams);
    }

    public function sync($namespace, $contentType)
    {
        if ( ! $contentType->get('es_type', false)) {
            return false;
        }

        // remember the original namespace so we can put it back later
        $originalProjectNamespace = $this->app['session']->get('project_namespace');
        $this->app['session']->set('project_namespace', $namespace);

        $contentTypeKey = $contentType->getKey();
        $contents = $this->app['repository.eloquent.' . $contentTypeKey]->get(array(), true, $namespace !== "trapps");
        foreach ($contents as $content) {
            $attributes = $content->toArray();

            if (isset($attributes['outgoing']['apps'])) {
                unset($attributes['outgoing']['apps']);
            }

            echo "inserting $contentTypeKey for $namespace<br>";
            var_dump($attributes);

            $this->app['repository.elasticsearch.' . $contentTypeKey]->store($attributes);
        }

        $this->app['session']->set('project_namespace', $originalProjectNamespace);
    }

    public function syncAll($namespace)
    {
        foreach ($this->app['contenttypes'] as $contentType) {
            $this->sync($namespace, $contentType);
        }
    }

}
