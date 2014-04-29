<?php

namespace Bolt\Core;

use Bolt\Core\Providers\Silex\TwigPathServiceProvider;
use Bolt\Core\Providers\Silex\ConfigServiceProvider;
use Bolt\Core\Providers\Silex\FieldTypeServiceProvider;
use Bolt\Core\Providers\Silex\FieldServiceProvider;
use Bolt\Core\Providers\Silex\RelationServiceProvider;
use Bolt\Core\Providers\Silex\ContentTypeServiceProvider;
use Bolt\Core\Providers\Silex\ContentServiceProvider;
use Bolt\Core\Providers\Silex\PathsServiceProvider;
use Bolt\Core\Providers\Silex\DatabaseServiceProvider;
use Bolt\Core\Providers\Silex\NotifyServiceProvider;
use Bolt\Core\Providers\Silex\SerializerServiceProvider;
use Bolt\Core\Providers\Silex\ViewServiceProvider;
use Bolt\Core\Providers\Silex\EloquentServiceProvider;
use Bolt\Core\Providers\Silex\MigratorServiceProvider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;

use Illuminate\Support\Facades\Facade;

use Whoops\Provider\Silex\WhoopsServiceProvider;

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

        $this->register(new WhoopsServiceProvider);
        $this->register(new NotifyServiceProvider);
        //$this->register(new PathsServiceProvider);
        $this->register(new TwigServiceProvider);
        $this->register(new TwigPathServiceProvider);
        $this->register(new ConfigServiceProvider);
        $this->register(new RelationServiceProvider);
        $this->register(new FieldTypeServiceProvider);
        $this->register(new FieldServiceProvider);
        $this->register(new ContentTypeServiceProvider);
        $this->register(new ContentServiceProvider);
        $this->register(new DatabaseServiceProvider);
        $this->register(new SerializerServiceProvider);
        $this->register(new ViewServiceProvider);
        $this->register(new EloquentServiceProvider);
        $this->register(new MigratorServiceProvider);
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
