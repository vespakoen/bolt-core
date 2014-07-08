<?php

namespace Bolt\Core\Field;

use Bolt\Core\App;
use Bolt\Core\Content\Content;
use Bolt\Core\FieldType\FieldType;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field extends ConfigObject implements ArrayableInterface
{
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

    public function setKey($key)
    {
        return $this->key = $key;
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

    public function getRules()
    {
        $type = $this->getType();

        $typeRules = $type->get('rules', array());
        $rules = $this->get('rules', array());
        $rules = array_merge($typeRules, $rules);

        return array($this->getKey() => $rules);
    }

    public function getPurpose()
    {
        $type = $this->getType();

        return $type->get('purpose');
    }

    public function addColumnTo($table, $key)
    {
        $type = $this->getType();
        $table->addColumn($key, $type->getType(), array_except($type->get('doctrine'), array('type')));

        if($this->hasIndex()) {
            $table->addIndex($key);
        }
    }

    public function hasIndex()
    {
        return $this->get('index', false);
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

    public function getViewForForm(Content $content = null, $locale = null)
    {
        $field = $this;
        $fieldType = $this->getType();
        $key = $this->getKey();
        if ( ! is_null($locale) && $this->get('multilanguage')) {
            $key = $key . '_' . $locale;
        }

        $fieldDefault = $this->get('default');

        $flashBag = $this->app['session']->getFlashBag();

        $value = $content->get($key, $fieldDefault);
        if ($input = $flashBag->peek('input')) {
            $value = array_get($input, $key, $value);
        }

        $allErrors = $flashBag->peek('errors');
        $errors = array_get($allErrors, $key, array());

        $view = 'fieldtypes/form/' . $fieldType->getKey();

        $context = compact(
            'key',
            'fieldType',
            'field',
            'content',
            'value',
            'errors',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function getViewForListing(Content $content = null)
    {
        $field = $this;
        $fieldType = $this->getType();
        $key = $this->getKey();
        $fieldDefault = $this->get('default');
        if($this->get('multilanguage')) {
            $value = $content->get($key . '_' . $this->app['locale'], $fieldDefault);
        } else {
            $value = $content->get($key, $fieldDefault);
        }

        $view = 'fieldtypes/listing/' . $fieldType->getKey();

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
