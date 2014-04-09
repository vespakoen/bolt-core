<?php

namespace Bolt\Core\Field\Factory;

class FieldCollection {

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function create($items = array())
	{
		$fieldCollectionClass = $this->getFieldCollectionClass();

		return new $fieldCollectionClass($items);
	}

	public function fromConfig($config)
	{
	    $this->validateConfig($config);

		$fieldCollectionClass = $this->getFieldCollectionClass();

	    $collection = new $fieldCollectionClass;

	    foreach($config as $key => $config) {
	        $collection->add($key, $config);
	    }

	    return $collection;
	}

	public function validateConfig($config)
	{
	    if(!is_array($config)) {
	        $this->app['notify']->error(sprintf('Invalid "fields" configuration given, configuration\'s root value must be of type array.', $key));
	    }
	}

	protected function getFieldCollectionClass()
	{
		return $this->app['config']->getRaw('app/classes/fieldcollection', 'Bolt\Core\Field\FieldCollection');
	}

}
