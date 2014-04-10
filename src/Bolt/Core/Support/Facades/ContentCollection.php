<?php

namespace Bolt\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bolt\Core\Content\Factory\ContentCollection
 */
class ContentCollection extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'contents.factory'; }

}
