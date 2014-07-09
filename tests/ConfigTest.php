<?php

use Silex\Application;

use Bolt\Core\Provider\Silex\ConfigServiceProvider;

class ConfigTest extends PHPUnit_Framework_TestCase {

	public function testConfigLocator()
	{
		$app = $this->getApp();

		// Locate a config file
		$actual = $app['config.locator']->locate('config');
		$expected = $app['config.directories'][0].'/config.yml';

		$this->assertEquals($actual, $expected);
	}

	public function testConfigLoader()
	{
		$app = $this->getApp();

		// Load a config file's contents
		$actual = $app['config.loader']->load('config');
		$expected = array(
			'key' => 'value',
			'nested' => array(
				'key' => 'value'
			)
		);

		$this->assertEquals($actual, $expected);
	}

	public function testConfig()
	{
		$app = $this->getApp();

		// Get's all config data
		$actual = $app['config']->get();
		$expected = array(
			'config' => array(
				'key' => 'value',
				'nested' => array(
					'key' => 'value'
				)
			)
		);
		$this->assertEquals($actual, $expected);

		// Get config of a specific file
		$actual = $app['config']->get('config');
		$expected = array(
			'key' => 'value',
			'nested' => array(
				'key' => 'value'
			)
		);
		$this->assertEquals($actual, $expected);

		// Get a nested config value
		$actual = $app['config']->get('config/nested/key');
		$expected = 'value';
		$this->assertEquals($actual, $expected);
	}

	protected function getApp()
	{
		// Create a container
		$app = new Application;

		// Register the config service
		$app->register(new ConfigServiceProvider);

		// Override some stuff
		$app['config.data'] = array(
			'key' => 'default-data',
			'new' => 'data'
		);

		$app['config.files'] = array(
			'config'
		);

		$app['config.directories'] = $app->share(function ($app) {
		    return array(__DIR__.'/stubs/config');
		});

		return $app;
	}

}

