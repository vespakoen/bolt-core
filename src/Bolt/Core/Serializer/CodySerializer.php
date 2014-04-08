<?php

namespace Bolt\Core\Serializer;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;

class CodySerializer {

	public function __construct($app)
	{
		$this->app = $app;
	}

	public function serialize()
	{
		$package = $this->getPackage();
		foreach($this->app['contenttypes'] as $contentType)
		{
			$model = $this->getModelForContentType($contentType);
			$package->addResource($model);

			$repository = $this->getRepositoryForContentType($contentType);
			$package->addResource($repository);
		}

		return $package;
	}

	protected function getPackage()
	{
		$config = $this->app['config'];

		$vendor = $config->get('app/package/vendor', 'MyApp');
		$name = $config->get('app/package/name', 'Domain');

		return new Package($vendor, $name);
	}

	protected function getModelForContentType($contentType)
	{
		$package = $this->getPackage();

		$type = 'model';

		$name = $this->getModelNameForContentType($contentType);

		$configuration = array(
			'base' => 'Bolt.Core.Models.Content',
			'table' => $contentType->getKey(),
			'columns' => $this->getModelColumns($contentType)
		);

		$compilers = array(
			'php-laravel'
		);

		return new Resource($package, $type, $name, $configuration, $compilers);
	}

	protected function getModelNameForContentType($contentType)
	{
		$package = $this->getPackage();

		$parts = array(
			$package->getVendor(),
			$package->getName(),
			'Models',
			ucfirst($contentType->getKey())
		);

		return implode('.', $parts);
	}

	protected function getModelColumns($contentType)
	{
		$columns = array();

		foreach($contentType->getFields() as $field) {
			$config = $field->getType()->getMigratorConfig();

			$columns[$field->getKey()] = array_merge(array(
				'type' => $config['type']
			), $config['options']);
		}

		return $columns;
	}

	protected function getRepositoryForContentType($contentType)
	{
		$package = $this->getPackage();

		$type = 'class';

		$name = $this->getRepositoryNameForContentType($contentType);

		$configuration = array(
			'base' => 'Bolt.Core.Repositories.EloquentRepository',
			'methods' => array(
				'__construct' => array(
					'returnType' => 'void',
					'parameters' => array(
						'model' => array(
							'type' => 'mixed',
							'comment' => 'The Model to be used'
						)
					),
					'content' => array(
						'php-core' => '$this->model = $model;'
					)
				)
			)
		);

		$compilers = array(
			'php-core'
		);

		return new Resource($package, $type, $name, $configuration, $compilers);
	}

	protected function getRepositoryNameForContentType($contentType)
 	{
 		$package = $this->getPackage();

 		$parts = array(
 			$package->getVendor(),
 			$package->getName(),
 			'Repositories',
 			ucfirst($contentType->getKey()).'Repository'
 		);

 		return implode('.', $parts);
 	}

}
