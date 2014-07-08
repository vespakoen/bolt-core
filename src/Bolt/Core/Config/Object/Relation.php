<?php

namespace Bolt\Core\Relation;

use Bolt\Core\App;
use Bolt\Core\Content\Content;
use Bolt\Core\Config\ConfigObject;
use Bolt\Core\Support\Facades\View;

class Relation extends ConfigObject
{
    protected $key;

    protected $type;

    protected $other;

    protected $options;

    public function __construct($app, $key, $other, $type, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->other = $other;
        $this->type = $type;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getOther()
    {
        return $this->other;
    }

    public function getType()
    {
        return $this->type;
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

        $context = compact(
            'relationType',
            'relation',
            'content',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function addColumnsTo($table)
    {
        //
    }

    protected function getDefaultOptions()
    {
        return array();
    }

}
