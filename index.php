<?php

// Autoloading
require "vendor/autoload.php";

// We need some helper methods from Laravel for convenience.
// At the time of writing this, I have only used "array_get" and, sorry to admit "dd" so far.
require "vendor/illuminate/support/Illuminate/Support/helpers.php";

// Use some stuff
use Bolt\Core\App;

// Create the app instance
$app = new App;

dd(App::make('config')->getData(), App::make('config')->get());

// $db = App::make('db');

// $schemaManager = $db->getSchemaManager();
// $fromSchema = $schemaManager->createSchema();

// $toSchema = clone $fromSchema;
// $toSchema->createTable('users')
// 	->addColumn("id", "integer");

// $sql = $fromSchema->getMigrateToSql($toSchema, $db->getDatabasePlatform());

// dd($sql);
