<?php

namespace Bolt\Core\Support;

use Illuminate\Support\Collection as IlluminateCollection;

class Collection extends IlluminateCollection {

	public function keys()
	{
		return array_keys($this->items);
	}

}
