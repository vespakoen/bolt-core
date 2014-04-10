<?php

namespace Bolt\Core\FieldType\Serializer;

class PassthroughSerializer implements SerializerInterface
{
    public function serialize($value)
    {
        return $value;
    }

    public function unserialize($value)
    {
        return $value;
    }

}
