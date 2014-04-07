<?php

namespace Bolt\Core;

use Silex\Provider\TwigServiceProvider;
use Bolt\Core\Providers\Silex\TwigPathServiceProvider;
use Bolt\Core\Providers\Silex\ConfigServiceProvider;
use Bolt\Core\Providers\Silex\FieldTypeServiceProvider;
use Bolt\Core\Providers\Silex\FieldServiceProvider;
use Bolt\Core\Providers\Silex\PathsServiceProvider;
use Bolt\Core\Providers\Silex\DatabaseServiceProvider;
use Bolt\Core\Providers\Silex\NotifyServiceProvider;

use Silex\Application;

use Whoops\Provider\Silex\WhoopsServiceProvider;
use Silex\Provider\DoctrineServiceProvider;

class App extends Application {

	protected static $app;

	public function __construct($values = array())
	{
		parent::__construct();

		static::$app = $this;

		foreach($values as $key => $value) {
		    $this[$key] = $value;
		}

		$this->register(new WhoopsServiceProvider);
		$this->register(new NotifyServiceProvider);
		$this->register(new PathsServiceProvider);
		$this->register(new TwigServiceProvider);
		$this->register(new TwigPathServiceProvider);
		$this->register(new ConfigServiceProvider);
		$this->register(new FieldTypeServiceProvider);
		$this->register(new FieldServiceProvider);
		$this->register(new DatabaseServiceProvider);
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
