<?php

namespace Bolt\Core\Migrator;

use Bolt\Core\Config\Object\Collection\ContentTypeCollection;

class Migrator
{
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function migrateTo(ContentTypeCollection $contentTypes)
    {
        $schemaManager = $this->db->getSchemaManager();
        $fromSchema = $schemaManager->createSchema();

        $toSchema = $contentTypes->getSchema();
        $queries = $fromSchema->getMigrateToSql($toSchema, $this->db->getDatabasePlatform());

        foreach ($queries as $query) {
           try {
                $this->db->query($query);
            } catch(\Exception $e) {
                // var_dump($e);
            }
        }
    }

}
