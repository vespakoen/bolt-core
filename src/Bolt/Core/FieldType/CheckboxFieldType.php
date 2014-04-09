<?php

namespace Bolt\Core\FieldType;

/**
 * CheckboxFieldType class
 */
class CheckboxFieldType extends FieldType {

    /**
     * The doctrine type name
     *
     * @var string
     */
    protected $doctrineType = 'boolean';

	/**
	 * Create a new CheckboxFieldType instance
	 *
	 * @param $app \Silex\Application
	 * @param $key string
	 * @param $serializer string
	 * @param $migrator Closure
	 */
    public function __construct($app, $key = 'checkbox', Closure $migrator = null)
    {
        parent::__construct($app, $key, $migrator);
    }

    /**
     * Get the default column options
     *
     * @return array
     */
    protected function getDefaultMigratorConfig()
    {
        return array(
            'default' => '0'
        );
    }

}
