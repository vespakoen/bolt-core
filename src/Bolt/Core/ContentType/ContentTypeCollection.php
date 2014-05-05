<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\ContentType;

use Doctrine\DBAL\Schema\Schema;

class ContentTypeCollection extends Collection
{
    public function addContentType($key, $contentType)
    {
        $this->items[$key] = $contentType;

        return $this;
    }

    public function add($key, $config)
    {
        $this->items[$key] = ContentType::fromConfig($key, $config);

        return $this;
    }

    public function addTablesTo($schema)
    {
        foreach ($this as $contentType) {
            $contentType->addTableTo($schema);
        }
    }

    public function getSchema()
    {
        $schema = new Schema;
        $this->addTablesTo($schema);

        return $schema;
    }

    public function findByOption($key, $value)
    {
        foreach($this as $contentType) {
            if($contentType->get($key) == $value) {
                return $contentType;
            }
        }
    }
}
