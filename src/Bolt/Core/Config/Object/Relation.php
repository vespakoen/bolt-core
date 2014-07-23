<?php

namespace Bolt\Core\Config\Object;

use Illuminate\Support\Contracts\ArrayableInterface;

use Bolt\Core\App;
use Bolt\Core\Config\Object\Content;
use Bolt\Core\Config\ConfigObject;
use Bolt\Core\Support\Facades\View;

class Relation extends ConfigObject implements ArrayableInterface
{
    protected $key;

    protected $type;

    protected $other;

    protected $options;

    public function __construct($app, $key, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getOther()
    {
        $other = $this->get('other');

        return $this->app['contenttypes']->get($other);
    }

    public function getType()
    {
        return $this->get('type');
    }

    public function getLabel()
    {
        if ($label = $this->get('label')) {
            return $label;
        }

        return ucfirst(str_replace(array('_', '-'), ' ', $this->getKey()));
    }

    public function validate()
    {
        $app = $this->app;

        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if ($this->key !== $cleaned) {
            $app['notify']->error(sprintf('Invalid relation key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    public function getViewForForm(Content $content = null)
    {
        $relation = $this;
        $relationKey = $this->getKey();
        $relationType = $this->getType();
        $relationDefault = $this->get('default');
        $view = 'relationtypes/form/' . strtolower($relationType);

        $contentTypeKey = $content->getContentType()->getKey();
        $otherKey = $relation->getOther()->getKey();
        $id = $content->getId();
        $name = $contentTypeKey . '[' . $id . '][links][' . $otherKey . '][]';
        $id = $contentTypeKey . '-' . $id . '-' . $otherKey;

        $context = compact(
            'relationType',
            'relation',
            'content',
            'view',
            'id',
            'name'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function addColumnsTo($table)
    {
        //
    }

    public function toArray()
    {
        return $this->options;
    }

    protected function getDefaultOptions()
    {
        return array();
    }

}
