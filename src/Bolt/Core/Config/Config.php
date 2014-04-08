<?php

namespace Bolt\Core\Config;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;

use Symfony\Component\Config\Loader\LoaderInterface;

class Config {

    protected $app;

    protected $rawLoader;

    protected $objectifiedLoader;

    protected $configs;

    protected $rawData;

    protected $data;

    public function __construct(App $app, LoaderInterface $rawLoader, LoaderInterface $objectifiedLoader, array $configs = array(), array $rawData = array())
    {
        $this->app = $app;
        $this->rawLoader = $rawLoader;
        $this->objectifiedLoader = $objectifiedLoader;
        $this->configs = $configs;
        $this->rawData = $this->getRawData();
    }

    public function getRaw($key = null, $default = null)
    {
        $key = str_replace('/', '.', $key);

        if(empty($key)) {
            return $this->rawData;
        }

        return array_get($this->rawData, $key);
    }

    public function get($key = null, $default = null)
    {
        $key = str_replace('/', '.', $key);

        if(is_null($this->data)) {
            $this->data = $this->getObjectifiedData()->toArray();
        }

        if(empty($key)) {
            return $this->data;
        }

        return array_get($this->data, $key, $default);
    }

    public function getRawData()
    {
        $rawData = array();

        foreach($this->configs as $as => $key) {
            $rawData[is_string($as) ? $as : $key] = $this->rawLoader->load($key);
        }

        return $rawData;
    }

    public function getObjectifiedData()
    {
        $data = new Collection;

        foreach($this->configs as $as => $key) {
            $data->put(is_string($as) ? $as : $key, $this->objectifiedLoader->load($key));
        }

        return $data;
    }

}
