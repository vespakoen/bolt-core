<?php

namespace Bolt\Core\ContentType\Loader;

use InvalidArgumentException;

use Bolt\Core\ContentType\ContentTypeCollection;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

use Illuminate\Support\Str;

class YamlContentTypeLoader extends FileLoader {

    /**
     * Loads a Yaml file.
     *
     * @param string      $file A Yaml file path
     * @param string|null $type The resource type
     *
     * @return \Bolt\Core\ContentType\ContentTypeCollection A ContentTypeCollection instance
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

        return ContentTypeCollection::fromConfig($config);
    }

    public function supports($resource, $type = null)
    {
        return Str::endsWith($resource, 'contenttypes.yml');
    }

}
