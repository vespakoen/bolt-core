<?php

use Silex\Application;

use Bolt\Core\Provider\Silex\ConfigServiceProvider;

class ConfigTest extends PHPUnit_Framework_TestCase {

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

		$app['config.files'] = array(
			'config'
		);

		$app['config.directories'] = array(__DIR__.'/stubs/config');

		return $app;
	}

}

