<?php

namespace Bolt\Core\Content\Factory;

class ContentCollection
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function create($items = array(), $contentType)
    {
        $contentCollectionClass = $this->getContentCollectionClass();

        $contents = array();
        foreach($items as $key => $item) {
            $contents[$key] = $this->app['content.factory']->create($item, $contentType);
        }

        return new $contentCollectionClass($contents);
    }

    public function validateConfig($config)
    {
        if (!is_array($config)) {
            $this->app['notify']->error(sprintf('Invalid "content" configuration given, configuration\'s root value must be of type array.', $key));
        }
    }

    protected function getContentCollectionClass()
    {
        return $this->app['config']->get('app/classes/contentcollection', 'Bolt\Core\Content\ContentCollection');
    }

}
