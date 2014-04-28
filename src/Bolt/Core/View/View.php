<?php

namespace Bolt\Core\View;

class View
{
    public function __construct($twig, $env, $file, $context = array(), $key = null)
    {
        $this->twig = $twig;
        $this->env = $env;
        $this->file = $file;
        $this->context = $context;

        if ( ! is_null($key) && ! empty($key)) {
            $this->file = $this->file . '/' . $key;
        }

        if ( ! is_null($this->env) && ! empty($this->env)) {
            $this->file = $this->file . '/' . $this->env;
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}
