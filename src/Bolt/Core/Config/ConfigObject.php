<?php

namespace Bolt\Core\Config;

class ConfigObject
{
    /**
     * Options allow non-system fields to be discoverable
     *
     * @var array
     */
    protected $options;

    /**
     * Gets the options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function hasOption($option)
    {
        return array_key_exists($option, $this->options);
    }

    public function getOption($key, $default = null)
    {
        return array_get($this->options, $key, $default);
    }

}
