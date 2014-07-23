<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeInsertEvent extends Event
{
    protected $parameters;

    protected $contentType;

    public function __construct($parameters, $contentType)
    {
        $this->parameters = $parameters;
        $this->contentType = $contentType;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

}
