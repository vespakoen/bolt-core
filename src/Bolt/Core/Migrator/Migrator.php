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

        $contentTypes = $this->app['contenttypes'];
        $toSchema = $contentTypes->getSchema();
        $queries = $fromSchema->getMigrateToSql($toSchema, $db->getDatabasePlatform());

        foreach ($queries as $query) {
            // if(ends_with($query, 'DROP uuid')) {
            //     $query = str_replace('DROP uuid', 'DROP id', $query);
            // }

            // if(ends_with($query, 'ALTER id TYPE UUID')) {
            //     $query = str_replace('ALTER id TYPE UUID', 'RENAME COLUMN uuid TO id', $query);
            // }

            // if(starts_with($query, 'DROP INDEX')) {
            //     continue;
            // }

           try {
                $db->query($query);
            } catch(\Exception $e) {
                var_dump($e);
            }
        }
    }

    protected function uuid()
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }
}
