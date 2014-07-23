<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class AfterInsertEvent extends Event
{
    protected $parameters;

    protected $contentType;

    protected $isSuccessful;

    public function __construct($parameters, $contentType, $isSuccessful)
    {
        $this->parameters = $parameters;
        $this->contentType = $contentType;
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

    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

}
