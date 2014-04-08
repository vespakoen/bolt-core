<?php

namespace Bolt\Core\Extension;

use Bolt\Core\App;
use Bolt\Core\Field\FieldCollection;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;

class Extension extends ConfigObject implements ArrayableInterface {

    protected $objectType = 'extension';

    protected $key;

    protected $enabled;

    protected $providers;

    public function __construct($app, $key, $enabled = false, $providers = array(), $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->enabled = $enabled;
        $this->providers = $providers;
        $this->options = $options;

        $this->validate();
    }

    public static function fromConfig($key, $config)
    {
        $app = App::instance();
        $enabled = $config['enabled'];
        $providers = array_get($config, 'providers', array());
        if(array_key_exists('provider', $config)) {
            $providers = array($config['provider']);
        }
        $options = array_get($options, 'options', array());

        return new static($app, $key, $enabled, $providers, $config);
    }

    /**
     * Gets the key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Indicates whether the extension is enabled
     *
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Gets the providers
     *
     * @return mixed
     */
    public function getProviders()
    {
        return $this->providers;
    }

    public function toArray()
    {
        return array(
            'key' => $this->getKey(),
            'enabled' => $this->getEnabled(),
            'providers' => $this->getProviders(),
            'config' => $this->getConfig(),
        );
    }

    /**
     * Validates the properties of the contenttype
     *
     * @return void
     */
    public function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid Extension key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }
    }

}
