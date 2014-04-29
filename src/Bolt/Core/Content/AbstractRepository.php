<?php

namespace Bolt\Core\Content;

use Bolt\Core\ContentType\ContentType;

abstract class AbstractRepository {

    public function __construct($app, ContentType $contentType)
    {
        $this->app = $app;
        $this->contentType = $contentType;
    }

    abstract public function getForListing($sort, $order, $search, $offset, $limit);

}
