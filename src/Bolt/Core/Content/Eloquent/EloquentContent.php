<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\Content\Content;

class EloquentContent extends Content {

	public function __get($key)
	{
		return $this->model->$key;
	}

}
