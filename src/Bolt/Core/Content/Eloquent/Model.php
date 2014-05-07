<?php

namespace Bolt\Core\Content\Eloquent;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Model extends Eloquent
{

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'datecreated';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'datechanged';

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'datedepublish';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array('datepublish');

    /**
     * Relationship with other contenttypes
     */
    public function links()
    {
        return $this->hasMany('Bolt\Core\Content\Eloquent\Link', 'from_id')
            ->where('from_contenttype', '=', get_class($this));
    }

}
