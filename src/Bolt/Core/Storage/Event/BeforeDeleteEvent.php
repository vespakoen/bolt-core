<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeDeleteEvent extends Event
{
    protected $request;

    protected $contentType;

    protected $id;

    public function __construct($request, $contentType, $id)
    {
        $this->request = $request;
        $this->contentType = $contentType;
        $this->id = $id;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function getId()
    {
        return $this->id;
    }

}
