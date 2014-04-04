<?php

namespace Bolt\Core\Config;

use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * ConfigLoaderResolver selects a loader for a given resource.
 *
 * @author Koen Schmeets <hello@koenschmeets.nl>
 */
class ConfigLoaderResolver extends LoaderResolver
{
    protected $locator;

    /**
     * Constructor.
     *
     * @param LoaderInterface[] $loaders An array of loaders
     */
    public function __construct($locator, array $loaders = array())
    {
        $this->locator = $locator;

        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    /**
     * Returns a loader able to load the resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return LoaderInterface|false A LoaderInterface instance
     */
    public function resolve($resource, $type = null)
    {
        if(false === $resource = $this->locator->locate($resource))
        {
            return false;
        }

        foreach ($this->getLoaders() as $loader) {
            if ($loader->supports($resource, $type)) {
                return $loader;
            }
        }

        return false;
    }

}
