<?php

namespace Bolt\Core\FieldType\Serializer;

interface SerializerInterface
{
    public function serialize($value);

    public function unserialize($value);

}
