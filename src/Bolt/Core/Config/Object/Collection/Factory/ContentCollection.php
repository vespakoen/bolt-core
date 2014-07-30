<?php

namespace Bolt\Core\Config\Object\Collection\Factory;

class ContentCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array(), $contentType, $total = null)
    {
        $contentCollectionClass = $this->getContentCollectionClass();

        $contents = array();
        foreach($items as $key => $item) {
            $contents[$key] = $this->app['content.factory']->create($item, $contentType);
        }

        return new $contentCollectionClass($contents, $total);
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "content" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function getContentCollectionClass()
    {
        return $this->app['config']->get('app/classes/contentcollection', 'Bolt\Core\Config\Object\Collection\ContentCollection');
    }

}
