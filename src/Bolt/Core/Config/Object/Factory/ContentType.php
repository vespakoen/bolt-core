<?php

namespace Bolt\Core\Config\Object\Factory;

use Bolt\Core\Config\Object\Collection\FieldCollection;
use Bolt\Core\Config\Object\Collection\RelationCollection;

use Illuminate\Support\Str;

class ContentType
{

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($key, $fields, $relations, $options)
    {
        $contentTypeClass = $this->getContentTypeClass();

        $options = $this->getDefaultOptions($key, $options);

        return new $contentTypeClass($this->app, $key, $fields, $relations, $options);
    }

    public function fromConfig($key, $config)
    {
        $fieldsConfig = array_get($config, 'fields', array());
        $fields = $this->app['fields.factory']->fromConfig($fieldsConfig);

        $relationsConfig = array_get($config, 'relations', array());
        $relations = $this->app['relations.factory']->fromConfig($relationsConfig);

        return $this->create($key, $fields, $relations, $config);
    }

    protected function getDefaultOptions($key, $options)
    {
        $defaults = $this->app['config']->get('defaults/contenttype', array());

        $options = array_replace_recursive($defaults, $options);

        if ( ! array_key_exists('slug', $options)) {
            $options['slug'] = $key;
        }

        $options['name'] = $this->app['translator']->trans('contenttype.plural.' . $key);

        $options['singular_name'] = $this->app['translator']->trans('contenttype.singular.' . $key);

        return $options;
    }

    protected function getContentTypeClass()
    {
        return $this->app['config']->get('app/classes/contenttype', 'Bolt\Core\Config\Object\ContentType');
    }

}
