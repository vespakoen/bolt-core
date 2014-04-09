<?php

namespace Bolt\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bolt\Core\View\Factory\View
 */
class View extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'view.factory'; }

}
