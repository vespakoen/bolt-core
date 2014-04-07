<?php

namespace Bolt\Core\FieldType;

use Doctrine\DBAL\Platforms\SqlitePlatform;

/**
 * TextFieldType class
 */
class TextFieldType extends FieldType {

	/**
	 * The doctrine type name
	 *
	 * @var string
	 */
	protected $doctrineType = 'text';

	/**
	 * Create a new TextFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $doctrineType string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'text', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorOptions()
    {
        $driver = $this->app['config']->getRaw('app/database/driver');

        $textDefault = null;
        if ($driver == "sqlite") {
            $textDefault = '';
        }

        return array(
            'default' => $textDefault
        );
    }

}
