<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\App;
use Bolt\Core\Support\Collection;
use Bolt\Core\Support\Facades\ContentType;

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
        $db = App::make('db');

        $schemaManager = $db->getSchemaManager();
        $schema = $schemaManager->createSchema();

        $this->addTablesTo($schema);

        return $schema;
    }

}
