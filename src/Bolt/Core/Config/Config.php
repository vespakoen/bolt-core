<?php

namespace Bolt\Core\Config;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;

use Symfony\Component\Config\Loader\LoaderInterface;

class Config {

    protected $app;

    protected $loader;

    protected $configs = array(
        'app',
        'fieldtypes',
        'contenttypes',
        'extensions',
        'routing',
    );

    protected $data;

    public function __construct(App $app, LoaderInterface $loader)
    {
        $this->app = $app;
        $this->loader = $loader;

        $this->data = $this->getData();
    }

    public function get($key = null, $default = null)
    {
        $array = $this->data->toArray();

        if(is_null($key)) {
            return $array;
        }

        $key = str_replace('/', '.', $key);

        return array_get($array, $key, $default);
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function setConfigs($configs = array())
    {
        $this->configs = $configs;
    }

    public function getData()
    {
        if( ! isset($this->data)) {
            $this->data = new Collection;

            foreach(array_except($this->getConfigs(), array('fieldtypes')) as $key) {
                $this->data->put($key, $this->loader->load($key));
            }

            $allFieldTypes = $this->app['fieldtypes']->merge($this->data->get('fieldtypes'));
            $this->data->put('fieldtypes', $allFieldTypes);
        }

        return $this->data;
    }

}
