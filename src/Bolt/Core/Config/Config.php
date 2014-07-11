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

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function get($key = null, $default = null)
    {
        $key = str_replace('/', '.', $key);

        if (empty($key)) {
            return $this->data;
        }

        return array_get($this->data, $key, $default);
    }

}
