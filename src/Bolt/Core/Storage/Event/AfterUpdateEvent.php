<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class AfterUpdateEvent extends Event
{
    protected $parameters;

    protected $contentType;

    protected $id;

    protected $isSuccessful;

    public function __construct($parameters, $contentType, $id, $isSuccessful)
    {
        $this->parameters = $parameters;
        $this->contentType = $contentType;
        $this->id = $id;
        $this->isSuccessful = $isSuccessful;
    }

    public function getParameters()
    {
        return $this->parameters;
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
