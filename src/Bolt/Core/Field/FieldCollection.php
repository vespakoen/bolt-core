<?php

namespace Bolt\Core\Field;

use InvalidArgumentException;

use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\Field;
use Bolt\Core\Support\Facades\Config;

class FieldCollection extends Collection
{
    protected $items = array();

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public static function fromConfig($config)
    {
        static::validate($config);

        $collection = new static;

        foreach ($config as $key => $config) {
            $collection->add($key, $config);
        }

        return $collection;
    }

    public function addField($key, $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = Field::fromConfig($key, $config);

        return $this;
    }

    public function addColumnsTo($table)
    {
        foreach ($this->getDatabaseFields() as $field) {
            $field->addColumnTo($table, $field->getKey());
        }
    }

    public function getDatabaseFields()
    {
        $fields = new static($this->items);

        $locales = Config::get('app/locales');

        $multilanguageFields = $fields->getMultilanguageFields();
        foreach ($multilanguageFields as $multilanguageField) {
            $fields->forget($multilanguageField->getKey());
            foreach ($locales as $locale => $name) {
                $translationField = clone $multilanguageField;
                $newKey = $multilanguageField->getKey() . '_' . $locale;
                $translationField->setKey($newKey);
                $fields->addField($newKey, $translationField);
            }
        }

        return $fields;
    }

    public function getPrimaryKeyFields()
    {
        return $this->filter(function($item) {
            return $item->getKey() == 'id';
        });
    }

    public function getNonPrimaryKeyFields()
    {
        return $this->filter(function($item) {
            return $item->getKey() !== 'id';
        });
    }

    public function getMultilanguageFields()
    {
        return $this->filter(function($item) {
            return $item->get('multilanguage', false) === true;
        });
    }

    public function getNonMultilanguageFields()
    {
        return $this->filter(function($item) {
            return $item->get('multilanguage', false) === false;
        });
    }

    public function filterByTypeKey($typeKey)
    {
        return $this->filterByTypeKeys(array($typeKey));
    }

    public function filterByKeys($keys)
    {
        return $this->filter(function($field) use ($keys) {
            return in_array($field->getKey(), $keys);
        });
    }

    public function filterByTypeKeys($typeKeys)
    {
        return $this->filter(function($field) use ($typeKeys) {
            return in_array($field->getType()->getKey(), $typeKeys);
        });
    }

    public function filterByOption($key, $value)
    {
        return $this->filter(function($field) use ($key, $value) {
            return $field->get($key) == $value;
        });
    }

    public function forPurpose($purpose)
    {
        return $this->filterByOption('purpose', $purpose)
            ->first();
    }

    public function getTextFields()
    {
        return $this->filterByTypeKeys(array(
            'string',
            'text',
            'textarea',
            'html',
            'markdown'
        ));
    }

    public static function validate($config)
    {
        if (!is_array($config)) {
            throw new InvalidArgumentException(sprintf('Invalid "fields" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

}
