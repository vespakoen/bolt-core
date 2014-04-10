<?php

namespace Bolt\Core\View\Factory;

use Bolt\Core\App;

class View
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($file, $context = array(), $key = null)
    {
        $viewClass = $this->getViewClass();

        return new $viewClass($this->app['twig'], $file, $context, $key);
    }

    protected function getViewClass()
    {
        return $this->app['config']->getRaw('app/classes/view', 'Bolt\Core\View\View');
    }

}
