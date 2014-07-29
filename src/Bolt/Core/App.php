<?php

namespace Bolt\Core;

use Bolt\Core\Provider\Silex\ConfigServiceProvider;

use Silex\Application;

use Illuminate\Support\Facades\Facade;

class App extends Application
{
    protected static $app;

    public function __construct($values = array())
    {
        parent::__construct();

        static::$app = $this;

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }
        Facade::setFacadeApplication($this);

        // register the configserviceprovider so we can access the config
        $this->register(new ConfigServiceProvider);

        // grab the providers from the config and load them
        $providers = $this['config']->get('app/providers');
        foreach ($providers as $provider) {
            $this->register(new $provider);
        }

        // set the locale (https://github.com/silexphp/Silex/issues/983)
        $locale = $values['locale'];
        if ($this['translator']) {
            $this['translator']->setlocale($locale);
        }

        // register fieldtypes from config
        $fieldTypesConfig = $this['config']->get('fieldtypes');
        if ($fieldTypesConfig) {
            $this['fieldtypes'] = $this['fieldtypes.factory']->fromConfig($fieldTypesConfig);
        }

        // register contenttypes from config
        $contentTypeConfig = $this['config']->get('contenttypes');
        if ($contentTypeConfig) {
            $this['contenttypes'] = $this['contenttypes.factory']->fromConfig($contentTypeConfig);
        }
    }

    public static function instance()
    {
        return static::$app;
    }

    public static function make($key)
    {
        return static::$app[$key];
    }

}
