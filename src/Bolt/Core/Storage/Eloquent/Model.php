<?php

namespace Bolt\Core\Storage\Eloquent;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Model as Eloquent;

use Bolt\Core\App;

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
    public function outgoing()
    {
        $config = App::make('config');
        $vendor = $config->get('app/package/vendor');
        $name = $config->get('app/package/name');

        return $this->hasMany("{$vendor}\\{$name}\Model\Eloquent\Relations", 'from_id')
            ->where('from_type', '=', $this->getTable());
    }

    public function incoming()
    {
        $config = App::make('config');
        $vendor = $config->get('app/package/vendor');
        $name = $config->get('app/package/name');

        return $this->hasMany("{$vendor}\\{$name}\Model\Eloquent\Relations", 'to_id')
            ->where('to_type', '=', $this->getTable());
    }

    // public function callBoltSetter($key, $value, $setter)
    // {
    //     switch ($setter) {
    //         case 'geojson_to_postgis':
    //             $this->attributes[$key] = new Expression("ST_GeomFromGeoJSON('" . $value . "')");
    //             break;

    //         case 'to_json_if_array':
    //             $this->attributes[$key] = is_array($value) || is_object($value) ? json_encode($value) : $value;
    //             break;
    //     }
    // }

    // public function callBoltGetter($value, $getter)
    // {
    //     switch($getter) {
    //         case 'from_json_if_json':
    //             return substr($value, 0, 1) == "{" || substr($value, 0, 1) == "[" ? json_decode($value) : $value;
    //             break;
    //     }

    //     return $value;
    // }

}
