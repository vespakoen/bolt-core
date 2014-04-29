<?php

namespace Bolt\Core\ContentType\Factory;

use Bolt\Core\Field\FieldCollection;
use Bolt\Core\Relation\RelationCollection;

class ContentType
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $name, FieldCollection $fields = null, RelationCollection $relations = null, $slug = null, $singularName = null, $singularSlug = null, $showOnDashboard = null, $sort = null, $defaultStatus = null, $options = array())
    {
        $contentTypeClass = $this->getContentTypeClass();

        return new $contentTypeClass($this->app, $key, $name, $fields, $relations, $slug, $singularName, $singularSlug, $showOnDashboard, $sort, $defaultStatus, $options);
    }

    public function fromConfig($key, $config = array())
    {
        $contentTypeClass = $this->getContentTypeClass();

        if (is_string($config)) {
            $contentTypeClass = $config;

            if ( ! class_exists($contentTypeClass)) {
                $this->app['notify']->error('Unknown class for contenttype: '.$contentTypeClass);
            }

            return new $contentTypeClass($this->app);
        }

        $name = $config['name'];
        $slug = array_get($config, 'slug');
        $singularName = array_get($config, 'singular_name');
        $singularSlug = array_get($config, 'singular_slug');
        $showOnDashboard = array_get($config, 'show_on_dashboard');
        $sort = array_get($config, 'sort');
        $defaultStatus = array_get($config, 'default_status');
        $options = array_except($config, array('fields', 'relations', 'name', 'slug', 'singular_name', 'singular_slug', 'show_on_dashboard', 'sort', 'default_status'));

        $fields = $this->app['fields.factory']->fromConfig($config['fields']);
        $relations = $this->app['relations.factory']->fromConfig(array_get($config, 'relations', array()));
        // $taxonomy = TaxonomyCollection::fromConfig(array_get($config, 'taxonomy', array()));

        return new $contentTypeClass($this->app, $key, $name, $fields, $relations, $slug, $singularName, $singularSlug, $showOnDashboard, $sort, $defaultStatus, $options);
    }

    protected function getContentTypeClass()
    {
        return $this->app['config']->get('app/classes/contenttype', 'Bolt\Core\ContentType\ContentType');
    }

}
