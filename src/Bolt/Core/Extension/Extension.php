<?php

namespace Bolt\Core\Extension;

use Bolt\Core\Field\FieldCollection;

use Illuminate\Support\Contracts\ArrayableInterface;

class Extension implements ArrayableInterface {

    protected $key;

    protected $enabled;

    protected $providers;

    protected $config;

    public function __construct($key, $enabled = false, $providers = array(), $config)
    {
        $this->key = $key;
        $this->enabled = $enabled;
        $this->providers = $providers;
        $this->config = $config;
    }

    public static function fromConfig($key, $config)
    {
        static::validate($key, $config);

        $enabled = $config['enabled'];
        $providers = array_get($config, 'providers', array());
        if(array_key_exists('provider', $config)) {
            $providers = array($config['provider']);
        }
        $config = array_get($config, 'config', array());

        return new static($key, $enabled, $providers, $config);
    }

    public static function validate($key, $config)
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $key);

        if($key !== $cleaned) {
            throw new InvalidArgumentException(sprintf('Invalid Extension key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $key));
        }
    }

    public function toArray()
    {
        return array(
            'key' => $this->key,
            'enabled' => $this->enabled,
            'providers' => $this->providers,
            'config' => $this->config
        );
    }

}
