<?php

namespace Bolt\Core\Config\Object\Collection;

use InvalidArgumentException;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Config\Object\Field;
use Bolt\Core\Support\Facades\Config;

class FieldCollection extends Collection
{
    protected $items = array();

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public function addField($key, Field $field)
    {
        $this->items[$key] = $field;

        return $this;
    }

    public function add($key, $config)
    {
        $fieldFactory = App::make('field.factory');

        $this->items[$key] = $fieldFactory->fromConfig($key, $config);

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
        $fields = $this->copy();

        $locales = Config::get('app/locales');

        $multilanguageFields = $fields->getMultilanguageFields();
        foreach ($multilanguageFields as $field) {
            $fields->forget($field->getKey());
            foreach ($locales as $locale => $name) {
                $copy = clone $field;
                $newKey = $field->getKey() . '_' . $locale;
                $copy->setKey($newKey);
                $fields->addField($newKey, $copy);
            }
        }

        return $fields;
    }

    public function getValidationFields()
    {
        $fields = $this->copy();

        $multilanguageFields = $fields->getMultilanguageFields();
        foreach ($multilanguageFields as $field) {
            $fields->forget($field->getKey());

            $copy = clone $field;
            $newKey = $field->getKey() . '_' . App::make('locale');
            $copy->setKey($newKey);
            $fields->addField($newKey, $copy);
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

    public function filterByTypeType($typeType)
    {
        return $this->filterByTypeTypes(array($typeType));
    }

    public function filterByTypeTypes($typeTypes)
    {
      return $this->filter(function($field) use ($typeTypes) {
        return in_array($field->getType()->getType(), $typeTypes);
      });
    }

    public function filterByOption($key, $value)
    {
        return $this->filter(function($field) use ($key, $value) {
            return $field->get($key) == $value;
        });
    }

    public function filterByGroup()
    {
        $groups = array_unique($this->listsOption('group'));

        $results = array();
        foreach($groups as $group) {
            $results[$group] = $this->filterByOption('group', $group);
        }

        return new Collection($results);
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
