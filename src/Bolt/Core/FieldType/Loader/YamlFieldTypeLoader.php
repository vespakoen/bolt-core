<?php

namespace Bolt\Core\FieldType\Loader;

use InvalidArgumentException;

use Bolt\Core\Support\Facades\FieldTypeCollection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Loader\FileLoader;

use Illuminate\Support\Str;

class YamlFieldTypeLoader extends FileLoader {

    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return \Bolt\Core\FieldType\FieldTypeCollection A FieldTypeCollection instance
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

        $config = Yaml::parse($path);

        return FieldTypeCollection::fromConfig($config);
    }

    public function supports($resource, $type = null)
    {
        return Str::endsWith($resource, 'fieldtypes.yml');
    }

}
