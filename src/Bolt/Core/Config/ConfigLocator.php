<?php

namespace Bolt\Core\Config;

use Symfony\Component\Config\FileLocator;

/**
 * ConfigLocator uses an array of pre-defined paths to find config files with any allowed extensions.
 *
 * @author Koen Schmeets <hello@koenschmeets.nl>
 */
class ConfigLocator extends FileLocator
{
    protected $allowedExtensions = array(
        'yml'
    );

    /**
     * Returns a full path for a given file name.
     *
     * @param mixed   $name        The file name to locate
     * @param string  $currentPath The current path
     * @param Boolean $first       Whether to return the first occurrence or an array of filenames
     *
     * @return string|array The full path to the file|An array of file paths
     *
     * @throws \InvalidArgumentException When file is not found
     */
    public function locate($name, $currentPath = null, $first = true)
    {
        foreach ($this->allowedExtensions as $allowedExtension) {
            $filepaths = array();
            if (null !== $currentPath && file_exists($file = $currentPath.DIRECTORY_SEPARATOR.$name.'.'.$allowedExtension)) {
                if (true === $first) {
                    return $file;
                }
                $filepaths[] = $file;
            }

            foreach ($this->paths as $path) {
                if (file_exists($file = $path.DIRECTORY_SEPARATOR.$name.'.'.$allowedExtension)) {
                    if (true === $first) {
                        return $file;
                    }
                    $filepaths[] = $file;
                }
            }
        }

        if (!$filepaths) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not exist (in: %s%s).', $name, null !== $currentPath ? $currentPath.', ' : '', implode(', ', $this->paths)));
        }

        return array_values(array_unique($filepaths));
    }

}
