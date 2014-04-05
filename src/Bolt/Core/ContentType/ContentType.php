<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\Field\FieldCollection;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;

class ContentType implements ArrayableInterface {

    public function __construct($key, $name, $singularName = null, FieldCollection $fields = null, $showOnDashboard = null, $sort = null, $defaultStatus = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->singularName = is_null($singularName) ? $this->guessSingularName() : $singularName;
        $this->fields = $fields;
        $this->showOnDashboard = is_null($showOnDashboard) ? true : $showOnDashboard;
        $this->sort = is_null($sort) ? $this->getDefaultSortColumn() : $sort;
        $this->defaultStatus = is_null($defaultStatus) ? $this->getDefaultDefaultStatus() : $defaultStatus;
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $name = $config['name'];
        $singularName = array_get($config, 'singular_name');
        $collection = FieldCollection::fromConfig($config['fields']);

        return new static($key, $name, $singularName, $collection);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            throw new InvalidArgumentException(sprintf('Invalid ContentType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }
    }

    public function toArray()
    {
        return array(
            'key' => $this->key,
            'name' => $this->name,
            'singular_name' => $this->singularName,
            'fields' => $this->fields->toArray(),
            'show_on_dashboard' => $this->showOnDashboard,
            'sort' => $this->sort,
            'default_status' => $this->defaultStatus,
        );
    }

    protected function guessSingularName()
    {
        return Str::singular($this->name);
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
