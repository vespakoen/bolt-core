<?php

namespace Bolt\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bolt\Core\FieldType\Factory\FieldTypeCollection
 */
class FieldTypeCollection extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'fieldtypes.factory'; }

}
