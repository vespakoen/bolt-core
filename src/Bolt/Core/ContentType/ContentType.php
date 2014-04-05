<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\Field\FieldCollection;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;

class ContentType implements ArrayableInterface {

    public function __construct($key, $name, FieldCollection $fields = null, $slug = null, $singularName = null, $singularSlug = null, $showOnDashboard = null, $sort = null, $defaultStatus = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->slug = is_null($slug) ? $this->guessSlug() : $slug;
        $this->singularName = is_null($singularName) ? $this->guessSingularName() : $singularName;
        $this->singularSlug = is_null($singularSlug) ? $this->guessSingularSlug() : $singularSlug;
        $this->fields = $fields;
        $this->showOnDashboard = is_null($showOnDashboard) ? true : $showOnDashboard;
        $this->sort = is_null($sort) ? $this->getDefaultSortColumn() : $sort;
        $this->defaultStatus = is_null($defaultStatus) ? $this->getDefaultDefaultStatus() : $defaultStatus;
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $name = $config['name'];
        $slug = array_get($config, 'slug');
        $singularName = array_get($config, 'singular_name');
        $singularSlug = array_get($config, 'singular_slug');
        $showOnDashboard = array_get($config, 'show_on_dashboard');
        $sort = array_get($config, 'sort');
        $defaultStatus = array_get($config, 'default_status');

        $fields = FieldCollection::fromConfig($config['fields']);

        return new static($key, $name, $fields, $slug, $singularName, $singularSlug, $showOnDashboard, $sort, $defaultStatus);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid ContentType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }

        if( ! array_key_exists('fields', $config)) {
            $this->app['notify']->error('Missing "fields" key in contenttype with key "'.$key.'"');
        }
    }

    public function toArray()
    {
        return array(
            'key' => $this->key,
            'name' => $this->name,
            'slug' => $this->slug,
            'singular_name' => $this->singularName,
            'singular_slug' => $this->singularSlug,
            'fields' => $this->fields->toArray(),
            'show_on_dashboard' => $this->showOnDashboard,
            'sort' => $this->sort,
            'default_status' => $this->defaultStatus,
        );
    }

    protected function guessSlug()
    {
        return $this->key;
    }

    protected function guessSingularName()
    {
        return Str::singular($this->name);
    }

    protected function guessSingularSlug()
    {
        return $this->singularName;
    }

    protected function getDefaultSortColumn()
    {
        return 'id';
    }

    protected function getDefaultDefaultStatus()
    {
        return 'draft';
    }

}
