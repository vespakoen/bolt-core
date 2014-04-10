<?php

namespace Bolt\Core\FieldType\Geometry;

use CrEOF\Spatial\DBAL\Types\BinaryParser;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

class GeometryParser
{
    protected $geometry;

    public function __construct($geometry)
    {
        $this->geometry = $geometry;
    }

    public static function fromBinary($binary)
    {
        $parser = new BinaryParser($binary);
        $parsed = $parser->parse();

        switch ($parsed['type']) {
            case 'LINESTRING':
               return new static(new LineString($parsed['value'], $parsed['srid']));
                break;

            case 'POINT':
                return new static(new Point($parsed['value'], $parsed['srid']));
                break;
        }
    }

    public static function fromGeoJSON($geoJSON)
    {
        $data = json_decode($geoJSON);
        $class = 'CrEOF\Spatial\PHP\Types\Geometry\\'.$data->type;
        $geometry = new $class($data->coordinates, $data->srid);

        return new static($geometry);
    }

    public function asGeometry()
    {
        return $this->geometry;
    }

    public function asSql()
    {
        $sridSQL = null;

        if (($srid = $this->geometry->getSrid()) !== null) {
            $sridSQL = sprintf('SRID=%d;', $srid);
        }

        return sprintf(
            '%s%s(%s)',
            $sridSQL,
            strtoupper($this->geometry->getType()),
            $this->geometry
        );
    }

    public function asGeoJSON()
    {
        $type = $this->geometry->getType();
        $points = $this->geometry->getPoints();
        $srid = $this->geometry->getSrid();

        $coordinates = array();
        foreach ($points as $point) {
            $x = $point->getX();
            $y = $point->getY();
            $coordinates[] = array($x, $y);
        }

        return json_encode(array(
            "type" => $type,
            "coordinates" => $coordinates,
            "srid" => $srid
        ));
    }

}
