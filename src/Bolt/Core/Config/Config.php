<?php

namespace Bolt\Core\Config;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;

use Symfony\Component\Config\Loader\LoaderInterface;

class Config
{
    protected $loader;

    protected $configs;

    protected $data;

    public function __construct(LoaderInterface $loader, array $configs = array(), array $data = array())
    {
        $this->loader = $loader;
        $this->configs = $configs;
        $this->data = $this->getData();
    }

    public function get($key = null, $default = null)
    {
        $key = str_replace('/', '.', $key);

        if (empty($key)) {
            return $this->data;
        }

        return array_get($this->data, $key, $default);
    }

    public function getData()
    {
        $data = array();

        foreach ($this->configs as $as => $key) {
            $data[is_string($as) ? $as : $key] = $this->loader->load($key);
        }

        return $data;
    }

}
