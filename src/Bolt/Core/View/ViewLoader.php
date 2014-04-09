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
     * @param  string $name string The name of the template to load
     *
     * @return string The template source code
     */
    function getSource($name)
    {
    	return file_get_contents($this->findTemplate($name));
    }

    function findTemplate($name)
    {
		$parts = explode('/', $name);

		if(count($parts) == 3)
		{
			list($type, $key, $template) = $parts;
		}
		else
		{
			list($type, $template) = $parts;
		}

		$loadPaths = array();
		foreach($this->paths as $path) {
			$baseParts = array(
				$path,
				$type,
			);

			if(isset($key)) {
				$parts = array_merge($baseParts, array(
					'custom',
					$key
				));

				$loadPaths[] = implode('/', $parts);
			}

			$parts = array_merge($baseParts, array(
				'custom'
			));

			$loadPaths[] = implode('/', $parts);
			$loadPaths[] = implode('/', $baseParts);
		}

		foreach($loadPaths as $path) {
			$file = $path.'/'.$template.'.twig';
			if(file_exists($file)) {
				return $file;
			}
		}
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param  string $name string The name of the template to load
     *
     * @return string The cache key
     */
    function getCacheKey($name)
    {
    	return $name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string    $name The template name
     * @param timestamp $time The last modification time of the cached template
     */
    function isFresh($name, $time)
    {
    	return false;
    }
}
