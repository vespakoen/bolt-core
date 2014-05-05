<?php

namespace Bolt\Core\Relation;

use Bolt\Core\App;
use Bolt\Core\Content\Content;
use Bolt\Core\Config\ConfigObject;
use Bolt\Core\Support\Facades\View;

use Illuminate\Support\Contracts\ArrayableInterface;

class Relation extends ConfigObject implements ArrayableInterface
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

    public function toArray()
    {
        return array_merge(array(
            'key' => $this->getKey(),
            'type' => $this->getType()->getKey(),
            'other' => $this->getOther()
        ), $this->getOptions());
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
        return $this->getViewFor('form', $content);
    }

    public function getViewForListing(Content $content = null)
    {
        return $this->getViewFor('listing', $content);
    }

    public function addColumnsTo($table)
    {
        //
    }

    protected function getViewFor($screen, $content)
    {
        $relation = $this;
        $relationKey = $this->getKey();
        $relationDefault = $this->get('default');
        $view = 'relationtypes/' . strtolower($relationType->getType()) . '/' . $screen;

        $context = compact(
            'relationType',
            'relation',
            'content',
            'view'
        );

        return View::create($view, $context);
    }

    protected function getDefaultOptions()
    {
        return array();
    }

}
