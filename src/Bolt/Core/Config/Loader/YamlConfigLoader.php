<?php

namespace Bolt\Core\Config\Loader;

use InvalidArgumentException;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Loader\FileLoader;

use Illuminate\Support\Str;

class YamlConfigLoader extends FileLoader {

    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return \Bolt\Core\Config\ConfigCollection A ConfigCollection instance
     *
     * @throws \InvalidArgumentException When a route can't be parsed because YAML is invalid
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        if (!stream_is_local($path)) {
            throw new InvalidArgumentException(sprintf('This is not a local file "%s".', $path));
        }

        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('File "%s" not found.', $path));
        }

        return Yaml::parse($path);
    }

    public function supports($resource, $type = null)
    {
        return Str::endsWith($resource, '.yml');
    }

}
