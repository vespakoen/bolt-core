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
use Bolt\Core\Providers\Silex\CompilerServiceProvider;
use Bolt\Core\Providers\Silex\ViewServiceProvider;
use Bolt\Core\Providers\Silex\EloquentServiceProvider;
use Bolt\Core\Providers\Silex\MigratorServiceProvider;
use Bolt\Core\Providers\Silex\IlluminateServiceProvider;
use Bolt\Core\Providers\Silex\ElasticsearchServiceProvider;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

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
        $this->register(new SessionServiceProvider);
        $this->register(new IlluminateServiceProvider);
        $this->register(new UrlGeneratorServiceProvider);
        $this->register(new TranslationServiceProvider);
        $this->register(new TwigPathServiceProvider);
        $this->register(new ConfigServiceProvider);
        $this->register(new RelationServiceProvider);
        $this->register(new FieldTypeServiceProvider);
        $this->register(new FieldServiceProvider);
        $this->register(new ContentTypeServiceProvider);
        $this->register(new ContentServiceProvider);
        $this->register(new DatabaseServiceProvider);
        $this->register(new CompilerServiceProvider);
        $this->register(new ViewServiceProvider);
        $this->register(new EloquentServiceProvider);
        $this->register(new MigratorServiceProvider);
        $this->register(new UrlGeneratorServiceProvider);
        $this->register(new ServiceControllerServiceProvider);
        $this->register(new ElasticsearchServiceProvider);

        $this->after(function() {
        });
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
