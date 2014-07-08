<?php

namespace Bolt\Core\View\Factory;

class View
{
    public function __construct($twig, $config)
    {
        $this->twig = $twig;
        $this->config = $config;
    }

    public function create($file, $context = array())
    {
        $viewClass = $this->getViewClass();

        return new $viewClass($this->twig, $file, $context);
    }

    protected function getViewClass()
    {
        return $this->config->get('app/classes/view', 'Bolt\Core\View\View');
    }

}
