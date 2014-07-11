<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeInsertEvent extends Event
{
    protected $request;

    protected $contentType;

    public function __construct($request, $contentType)
    {
        $this->request = $request;
        $this->contentType = $contentType;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

}
