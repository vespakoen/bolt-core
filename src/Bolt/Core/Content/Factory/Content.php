<?php

namespace Bolt\Core\Content\Factory;

class Content {

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function create($contentType, $model, $type = 'array')
	{
		$contentClass = $this->getContentClass($type);

		return new $contentClass($this->app, $contentType, $model);
	}

	public function fromConfig($key, $config = array())
	{
		$this->validateConfig($key, $config);

		$contentClass = $this->getContentClass('array');
		$contentType = $this->app['contenttypes']->get($config['contenttype']);
		$model = $config['data'];

	    return new $contentClass($this->app, $contentType, $model);
	}

	public function validateConfig($key, $config)
	{
	    // @todo implement
	}

	protected function getContentClass($type)
	{
		return $this->app['config']->getRaw('app/classes/content/' . $type . '/content', 'Bolt\Core\Content\Content');
	}

}
