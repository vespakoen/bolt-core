<?php

namespace Bolt\Core\FieldType\Factory;

use Bolt\Core\App;

class FieldType {

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function create($app, $key, Closure $migrator = null, $options = array())
	{
		$fieldTypeClass = $this->getFieldTypeClass();

		return new $fieldTypeClass($app, $key, $migrator, $options);
	}

	public function fromConfig($key, $config = array())
	{
		$fieldTypeClass = $this->getFieldTypeClass();

		if( ! is_string($config)) {
		    $fieldTypeClass = $config;

		    if( ! class_exists($fieldTypeClass)) {
		        $this->app['notify']->error('Unknown class for fieldtype: '.$fieldTypeClass);
		    }

		    return new $fieldTypeClass($this->app);
		}

		$migrator = array_get($config, 'migrator');

		if(!is_null($migrator)) {
		    $migrator = function($table, $field) use ($migrator) {
		        $key = $field->getKey();

		        $type = array_get($migrator, 'type', 'string');
		        $options = array_get($migrator, 'options', array());

		        $table->addColumn($key, $type, $options);
		    };
		}

		return new $fieldTypeClass($this->app, $key, $migrator);
	}

	protected function getFieldTypeClass()
	{
		return $this->app['config']->getRaw('app/classes/fieldtype', 'Bolt\Core\FieldType\FieldType');
	}

}
