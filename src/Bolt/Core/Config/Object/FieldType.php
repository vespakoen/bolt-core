<?php

namespace Bolt\Core\Config\Object;

use Bolt\Core\App;
use Bolt\Core\Field\Field;
use Bolt\Core\Config\ConfigObject;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;

use Illuminate\Support\Contracts\ArrayableInterface;

class FieldType extends ConfigObject implements ArrayableInterface
{
    /**
     * Bolt's key for the FieldType
     * Examples: image,geolocation,html,text etc
     */
    protected $key;

    /**
     * Create a new FieldType instance
     *
     * @param $app The application container
     * @param $key The key that will be used within bolt
     * @param $options array Options for doctrine
     */
    public function __construct($app, $key, $type, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->type = $type;
        $this->options = $options;

        $this->validate();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMigratorConfig()
    {
        return array_merge(array(
            'type' => $this->getType()
        ), $this->options);
    }

    public function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", '', $this->key);

        if ($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid FieldType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

    public function toArray()
    {
        return array_merge($this->options, array(
            'key' => $this->getKey()
        ));
    }

}
