<?php

namespace Bolt\Core;

use Bolt\Core\Provider\Silex\TwigPathServiceProvider;
use Bolt\Core\Provider\Silex\ConfigServiceProvider;
use Bolt\Core\Provider\Silex\FieldTypeServiceProvider;
use Bolt\Core\Provider\Silex\FieldServiceProvider;
use Bolt\Core\Provider\Silex\RelationServiceProvider;
use Bolt\Core\Provider\Silex\ContentTypeServiceProvider;
use Bolt\Core\Provider\Silex\ContentServiceProvider;
use Bolt\Core\Provider\Silex\PathsServiceProvider;
use Bolt\Core\Provider\Silex\DatabaseServiceProvider;
use Bolt\Core\Provider\Silex\NotifyServiceProvider;
use Bolt\Core\Provider\Silex\CompilerServiceProvider;
use Bolt\Core\Provider\Silex\ViewServiceProvider;
use Bolt\Core\Provider\Silex\EloquentServiceProvider;
use Bolt\Core\Provider\Silex\MigratorServiceProvider;
use Bolt\Core\Provider\Silex\IlluminateServiceProvider;
use Bolt\Core\Provider\Silex\ElasticsearchServiceProvider;
use Bolt\Core\Provider\Silex\ControllerServiceProvider;

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
        $this->register(new ControllerServiceProvider);

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
