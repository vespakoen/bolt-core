<?php

namespace Bolt\Core\Support;

use InvalidArgumentException;

use Bolt\Core\App;

class Notify {

	protected $notifications;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	public function error($developerError, $userError = null)
	{
		$devMode = $this->app['config']->getRaw('app/debug', false);

		if($devMode) {
			throw new InvalidArgumentException($developerError);
		}
		elseif(!is_null($userError)) {
			$this->notification['errors'][] = $userError;
		}
	}

}
