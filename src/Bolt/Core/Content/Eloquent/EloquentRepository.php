<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\Support\Facades\Content;
use Bolt\Core\Support\Facades\ContentCollection;

class EloquentRepository
{

	/**
	 * @param mixed $model       The Model to be used
	 * @param mixed $contentType The ContentType configuration object
	 *
	 * @return void
	 */
	public function __construct($model, $contentType)
	{
		$this->model = $model;
		$this->contentType = $contentType;
	}

	/**
	 * @param array $options Options for retrieving the content
	 *
	 * @return \Bolt\Core\Content\ContentCollection
	 */
	public function getForListing($options)
	{
		$models = $this->model->get();

		$contents = array();
		foreach ($models as $model) {
			$contents[] = Content::create($this->contentType, $model, 'eloquent');
		}

		return ContentCollection::create($contents);
	}

}
