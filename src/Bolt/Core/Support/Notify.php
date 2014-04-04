<?php

namespace Bolt\Core\Support;

class Notify {

	public static function error($developerError, $userError = null)
	{
		$devMode = Config::get('general/dev', false);

		if($devMode) {
			throw new Exception($developerError);
		}
		elseif(!is_null($userError)) {
			App::make('notify')->error($userError);
		}
	}

}
