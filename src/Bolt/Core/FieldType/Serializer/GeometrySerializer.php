<?php

namespace Bolt\Core\FieldType\Serializer;

use Bolt\Core\FieldType\Geometry\GeometryParser;

class GeometrySerializer implements SerializerInterface
{
    public function serialize($value)
    {
        $parser = GeometryParser::fromGeoJSON($value);

        return $parser->asSql();
    }

    public function unserialize($value)
    {
        $parser = GeometryParser::fromBinary($value);

        return $parser->asGeoJSON();
    }

}
