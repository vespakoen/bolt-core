<?php

namespace Bolt\Core\Field;

use Bolt\Core\App;
use Bolt\Core\Content\Content;
use Bolt\Core\FieldType\FieldType;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field extends ConfigObject implements ArrayableInterface
{
    protected $objectType = 'field';

    protected $key;

    protected $type;

    protected $options;

    public function __construct($app, $key, FieldType $type = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->type = is_null($type) ? $this->getDefaultType() : $type;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
    }

    public static function getReservedFieldNames()
    {
        return array(
            // 'id',
            // 'slug',
            // 'datecreated',
            // 'datechanged',
            // 'datepublish',
            // 'datedepublish',
            // 'ownerid',
            // 'username',
            // 'status',
            // 'link'
        );
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getType()
    {
        return $this->type;
    }

    public function addColumnTo($table, $key)
    {
        $type = $this->getType();
        $table->addColumn($key, $type->getType(), $type->getOptions());

        if($this->hasIndex()) {
            $table->addIndex($key);
        }
    }

    public function addColumnsTo($table)
    {
        if ($this->getOption('multilanguage', false)) {
            $locales = $this->app['config']->get('app/locales');
            foreach ($locales as $locale => $name) {
                $key = $this->getKey().'_'.$locale;
                $this->addColumnTo($table, $key);
            }
        } else {
            $this->addColumnTo($table, $this->getKey());
        }
    }

    public function hasIndex()
    {
        return $this->getOption('index', false);
    }

    public function toArray()
    {
        return array_merge(array(
            'key' => $this->getKey(),
            'type' => $this->getType()->getKey()
        ), $this->getOptions());
    }

    public function validate()
    {
        $app = $this->app;

        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if ($this->key !== $cleaned) {
            $app['notify']->error(sprintf('Invalid field key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }

        if ($this->type->getKey() !== 'slug' && in_array($this->key, static::getReservedFieldNames())) {
            $app['notify']->error(sprintf('Invalid key for Field "%s". It may NOT be named as the following reserved field names '.implode(',', static::getReservedFieldNames()).'.', $this->key));
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

    protected function getViewFor($screen, $content)
    {
        $field = $this;
        $fieldType = $this->getType();
        $key = $this->getKey();
        $fieldDefault = $this->getOption('default');
        if($this->get('multilanguage')) {
            $value = $content->get($key . '_' . $this->app['locale'], $fieldDefault);
        } else {
            $value = $content->get($key, $fieldDefault);
        }

        $view = 'fieldtypes/' . $screen . '/' . $fieldType->getKey();

        $context = compact(
            'key',
            'fieldType',
            'field',
            'content',
            'value',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    protected function getDefaultType()
    {
        return $this->app['fieldtypes']->get('string');
    }

    protected function getDefaultOptions()
    {
        return array(
            'label' => $this->guessLabel(),
            'class' => '',
            'variant' => '',
            'default' => '',
            'pattern' => '',
            'index' => false,
        );
    }

    protected function guessLabel()
    {
        return ucfirst(str_replace('_', ' ', $this->key));
    }

}
