<?php

namespace Bolt\Core\Config\Object;

use Bolt\Core\App;
use Bolt\Core\Config\Object\Content;
use Bolt\Core\Config\Object\FieldType;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;

class Field extends ConfigObject implements ArrayableInterface
{
    protected $key;

    protected $type;

    protected $options;

    public function __construct($app, $key, FieldType $type, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->type = $type;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
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
        return $this->get('label');
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
        return array_merge($this->options, array(
            'key' => $this->getKey()
        ));
    }

    public function validate()
    {
        $app = $this->app;

        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if ($this->key !== $cleaned) {
            $app['notify']->error(sprintf('Invalid field key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    public function getInputKey($locale = null)
    {
        $key = $this->getKey();
        if ( ! is_null($locale) && $this->get('multilanguage')) {
            $key = $key . '_' . $locale;
        }

        return $key;
    }

    public function getInputName(Content $content, $locale = null)
    {
        $contentTypeKey = $content->getContentType()
            ->getKey();

        $id = $content->getId();

        $key = $this->getInputKey($locale);

        return $contentTypeKey . '[' . $id . '][' . $key . ']';
    }

    public function getInputId(Content $content, $locale = null)
    {
        $name = $this->getInputName($content, $locale);

        return str_replace(array('][', '[', ']'), array('-', '-', ''), $name);
    }

    public function getViewForForm(Content $content, $locale = null, $options = array())
    {
        $this->mergeOptions($options);

        $key = $this->getInputKey($locale);
        $id = $this->getInputId($content, $locale);
        $name = $this->getInputName($content, $locale);

        $flashBag = $this->app['session']->getFlashBag();

        $fieldDefault = $this->get('default');
        $value = $content->get($key, $fieldDefault);

        if ($input = $flashBag->peek('input')) {
            $value = array_get($input, $key, $value);
        }

        $allErrors = $flashBag->peek('errors');
        $errors = array_get($allErrors, $key, array());

        $fieldType = $this->getType();
        $view = 'fieldtypes/form/' . $fieldType->getKey();

        $fieldAttributes = $this->get('attributes', array());
        $attributes = array_merge($fieldAttributes, array(
            'id' => $id,
            'name' => $name
        ));

        $field = $this;
        $context = compact(
            'fieldType',
            'field',
            'attributes',
            'content',
            'value',
            'errors',
            'view',
            'name',
            'id'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function getViewForListing(Content $content = null)
    {
        $field = $this;
        $fieldType = $this->getType();
        $fieldDefault = $this->get('default');

        $key = $this->getInputKey($this->app['locale']);
        $value = $content->get($key);

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
