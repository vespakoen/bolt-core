<?php

namespace Bolt\Core\View;

class View {

	public function __construct($twig, $file, $context = array(), $key = null)
	{
		$this->twig = $twig;
		$this->file = $file;
		$this->context = $context;

		if( ! is_null($key)) {
			$this->file = $this->file.'/'.$key;
		}
	}

	public function render()
	{
		return $this->twig->render($this->file, $this->context);
	}

	public function __toString()
	{
		try {
			return $this->render();
		} catch(\Exception $e) {
			return $e->getMessage();
		}
	}

}
