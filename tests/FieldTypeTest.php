<?php

use Silex\Application;

use Bolt\Core\Provider\Silex\PathsServiceProvider;
use Bolt\Core\Provider\Silex\ConfigServiceProvider;
use Bolt\Core\Provider\Silex\FieldTypeServiceProvider;

class FieldTypeTest extends PHPUnit_Framework_TestCase {

	public function testCanCreateFieldType()
	{
		$app = $this->getApp();

		$fieldType = $app['fieldtype.factory']->create('image', 'string', array(
			"default" => "testvalue",
			"length" => 256
		));

		$this->assertInstanceOf('Bolt\Core\FieldType\FieldType', $fieldType);

		$this->assertEquals('image', $fieldType->getKey());
		$this->assertEquals('string', $fieldType->getType());
		$this->assertEquals('testvalue', $fieldType->get('default'));
	}

	protected function getApp()
	{
		// Create a container
		$app = new Application;

		// Register some services
		$app->register(new PathsServiceProvider);

		$app->register(new ConfigServiceProvider);

		$app['config.directories'] = $app->share(function ($app) {
		    return array(__DIR__.'/stubs/config');
		});

		$app['config.files'] = array(
			'config'
		);

		$app->register(new FieldTypeServiceProvider);

		return $app;
	}

}

