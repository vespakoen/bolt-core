<?php

namespace Bolt\Core\Migrator;

class Migrator
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function migrate()
    {
        $db = $this->app['db'];
        $schemaManager = $db->getSchemaManager();
        $fromSchema = $schemaManager->createSchema();
        $toSchema = $this->app['contenttypes']->getSchema();
        $queries = $fromSchema->getMigrateToSql($toSchema, $db->getDatabasePlatform());
        foreach($queries as $query) {
            $db->query($query);
        }
    }
}
