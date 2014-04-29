<?php

namespace Bolt\Core\View;

class View
{
    public function __construct($twig, $env, $file, $context = array())
    {
        $this->twig = $twig;
        $this->env = $env;
        $this->file = $file;
        $this->context = $context;
    }

    public function render()
    {
        return $this->twig->render($this->file, $this->context);
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
