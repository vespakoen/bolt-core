<?php

namespace Bolt\Core\Storage\Event;

use Symfony\Component\EventDispatcher\Event;

class BeforeUpdateEvent extends Event
{
    protected $parameters;

    protected $contentType;

    protected $id;

    public function __construct($parameters, $contentType, $id)
    {
        $this->parameters = $parameters;
        $this->contentType = $contentType;
        $this->id = $id;
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

}
