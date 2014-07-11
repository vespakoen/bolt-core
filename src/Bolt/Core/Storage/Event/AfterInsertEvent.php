<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class AfterInsertEvent extends Event
{
    protected $request;

    protected $contentType;

    protected $isSuccessful;

    public function __construct($request, $contentType, $isSuccessful)
    {
        $this->request = $request;
        $this->contentType = $contentType;
        $this->isSuccessful = $isSuccessful;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

}
