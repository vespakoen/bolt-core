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

    public function get($key, $default = null)
    {
        $key = str_replace('/', '.', $key);

        return array_get($this->options, $key, $default);
    }

    /**
     * Convenience magic for twig
     *
     * Allows 'name' to be used in stead of getName'
     * in the following example
     * <code>
     * {{ contentType.name }}
     * </cody>
     */
    public function __call($key, $value = null)
    {
        // Check if the key is present in the options
        $options = $this->getOptions();
        if (array_key_exists($key, $options)) {
            return $options[$key];
        }

        // Check if a getter exits
        $methodName = 'get'.ucfirst($key);
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }

        return null;
    }

}
