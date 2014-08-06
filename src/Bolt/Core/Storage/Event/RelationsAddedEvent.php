<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class RelationsAddedEvent extends Event
{
    protected $contentType;

    protected $ids;

    public function __construct($contentType, $ids)
    {
        $this->contentType = $contentType;
        $this->ids = $ids;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getIds()
    {
        return $this->ids;
    }

}
