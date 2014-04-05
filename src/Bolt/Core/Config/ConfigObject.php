<?php

namespace Bolt\Core\Config;

class ConfigObject {

	/**
	 * Options allow non-system fields to be discoverable
	 *
	 * @var array
	 */
	protected $options;

	public function serialize($strategy)
	{
		$key = 'serializers/' . $strategy . '/' . $this->objectType;

		$serializerClass = $this->app['config']->getRaw($key);
		if(is_null($serializerClass)) {
			$this->app['notify']->error('Unknown serializer requested: "' . $key . '"');
		}

		$serializer = new $serializerClass;

		return $serializer->serialize($this);
	}

	/**
	 * Gets the options
	 *
	 * @return array
	 */
	public function getOptions()
	{
	    return $this->options;
	}

	public function hasOption($option)
	{
	    return array_key_exists($option, $this->options);
	}

	public function getOption($key, $default = null)
	{
		return array_get($this->options, $key, $default);
	}

	public function toArray()
	{
	    return $this->serialize('array');
	}

}
