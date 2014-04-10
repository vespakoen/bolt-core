<?php

namespace Bolt\Core\FieldType\Serializer;

class JsonSerializer implements SerializerInterface
{
    public function serialize($value)
    {
        return json_encode($value);
    }

    public function unserialize($value)
    {
        return json_decode($value, true);
    }

}
