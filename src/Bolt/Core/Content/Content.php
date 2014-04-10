<?php

namespace Bolt\Core\Content;

class Content {

	public function __construct($app, $contentType, $model)
	{
		$this->app = $app;
		$this->contentType = $contentType;
		$this->model = $model;
	}

	protected function getTypeForKey($key)
	{
		$fields = $this->contentType->getFields();
		$field = $fields->get($key);

		return $field->getType();
	}

}
