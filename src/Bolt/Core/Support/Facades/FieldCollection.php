<?php

namespace Bolt\Core\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bolt\Core\Field\Factory\FieldCollection
 */
class FieldCollection extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'fields.factory'; }

}
