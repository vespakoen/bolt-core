<?php

namespace Bolt\Core\Controller\Event;

use Symfony\Component\EventDispatcher\Event;

class AfterUpdateEvent extends Event
{
    protected $request;

    protected $contentType;

    protected $id;

    protected $isSuccessful;

    public function __construct($request, $contentType, $id, $isSuccessful)
    {
        $this->request = $request;
        $this->contentType = $contentType;
        $this->id = $id;
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

    public function getId()
    {
        return $this->id;
    }

    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

}
