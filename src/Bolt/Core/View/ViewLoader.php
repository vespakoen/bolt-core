<?php

namespace Bolt\Core\View;

use Twig_LoaderInterface;

class ViewLoader implements Twig_LoaderInterface
{
    /**
     * Constructor.
     *
     * @param string|array $paths A path or an array of paths where to look for templates
     */
    public function __construct($paths = array())
    {
        $this->paths = $paths;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name string The name of the template to load
     *
     * @return string The template source code
     */
    public function getSource($name)
    {
        return file_get_contents($this->findTemplate($name));
    }

    public function findTemplate($name)
    {
        $parts = explode('/', $name);

        switch (count($parts)) {
            case 2:
                $key = null;
                $env = null;
                list($type, $template) = $parts;
                break;
            case 3:
                $env = null;
                list($type, $template, $key) = $parts;
                break;
            case 4:
                list($type, $template, $key, $env) = $parts;
                break;
        }

        $files = array();
        foreach ($this->paths as $path) {
            for ($i = 0; $i < 3; $i++) {
                $baseParts = array(
                    $path,
                    $type,
                );

                if (!is_null($env) && $i <= 1) {
                    $baseParts[] = $env;
                }

                if (!is_null($key) && $i <= 0) {
                    $baseParts[] = $key;
                }

                $baseParts[] = $template.'.twig';
                $files[] = implode('/', $baseParts);
            }
        }

        $files = array_unique($files);
        foreach ($files as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return $this->paths[1].'/not-found.twig';
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name string The name of the template to load
     *
     * @return string The cache key
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     */
    public function isFresh($name, $time)
    {
        return false;
    }
}
