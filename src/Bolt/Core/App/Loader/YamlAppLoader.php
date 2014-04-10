<?php

namespace Bolt\Core\App\Loader;

use InvalidArgumentException;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

use Illuminate\Support\Str;

class YamlAppLoader extends FileLoader
{
    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return array the app configuration
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
        return Str::endsWith($resource, 'app.yml') || Str::endsWith($resource, 'config.yml');
    }

}
