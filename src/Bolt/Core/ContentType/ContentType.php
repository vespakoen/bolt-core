<?php

namespace Bolt\Core\ContentType;

use Bolt\Core\App;
use Bolt\Core\Field\FieldCollection;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;

/**
 * A ContentType defines a resource and can be used to build Forms and Listings
 */
class ContentType extends ConfigObject implements ArrayableInterface {

    /**
     * The object type, used by the ConfigObject when serializing
     *
     * @var string
     */
    protected $objectType = 'contenttype';

    /**
     * The key that uniquely identifies the content type
     *
     * @var string
     */
    protected $key;

    /**
     * The name
     *
     * @var string
     */
    protected $name;

    /**
     * The slug
     *
     * @var string
     */
    protected $slug;

    /**
     * The singular version of the name
     *
     * @var string
     */
    protected $singularName;

    /**
     * The singular, slugified version of the name
     *
     * @var string
     */
    protected $singularSlug;

    /**
     * The fields for this content type
     *
     * @var \Bolt\Core\Field\FieldCollection
     */
    protected $fields;

    /**
     * Whether the content type should be listed on the dashboard
     *
     * @var bool
     */
    protected $showOnDashboard;

    /**
     * The initial sort column
     *
     * @var string
     */
    protected $sort;

    /**
     * The default status
     *
     * @var string
     */
    protected $defaultStatus;

    /**
     * Create a new ContentType instance
     *
     * @param $key
     * @param $name
     * @param $fields \Bolt\Core\Field\FieldCollection
     * @param $slug
     * @param $singularName
     * @param $singularSlug
     * @param $showOnDashboard
     * @param $sort
     * @param $defaultStatus
     *
     * @return \Bolt\Core\ContentType\ContentType
     */
    public function __construct($app, $key, $name, FieldCollection $fields = null, $slug = null, $singularName = null, $singularSlug = null, $showOnDashboard = null, $sort = null, $defaultStatus = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->name = $name;
        $this->slug = is_null($slug) ? $this->guessSlug() : $slug;
        $this->singularName = is_null($singularName) ? $this->guessSingularName() : $singularName;
        $this->singularSlug = is_null($singularSlug) ? $this->guessSingularSlug() : $singularSlug;
        $this->fields = $fields;
        $this->showOnDashboard = is_null($showOnDashboard) ? true : $showOnDashboard;
        $this->sort = is_null($sort) ? $this->getDefaultSort() : $sort;
        $this->defaultStatus = is_null($defaultStatus) ? $this->getDefaultDefaultStatus() : $defaultStatus;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
    }

    /**
     * Initialize a ContentType object from a config
     *
     * @param $key
     * @param $config
     *
     * @return \Bolt\Core\ContentType\ContentType
     */
    public static function fromConfig($key, $config)
    {
        $app = App::instance();
        $name = $config['name'];
        $slug = array_get($config, 'slug');
        $singularName = array_get($config, 'singular_name');
        $singularSlug = array_get($config, 'singular_slug');
        $showOnDashboard = array_get($config, 'show_on_dashboard');
        $sort = array_get($config, 'sort');
        $defaultStatus = array_get($config, 'default_status');
        $options = array_except($config, array('name', 'slug', 'singular_name', 'singular_slug', 'show_on_dashboard', 'sort', 'default_status'));

        $fields = FieldCollection::fromConfig($config['fields']);
        // $relations = RelationCollection::fromConfig(array_get($config, 'relations', array()));
        // $taxonomy = TaxonomyCollection::fromConfig(array_get($config, 'taxonomy', array()));

        return new static($app, $key, $name, $fields, $slug, $singularName, $singularSlug, $showOnDashboard, $sort, $defaultStatus, $options);
    }

    /**
     * Gets the key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Gets the singularName.
     *
     * @return string
     */
    public function getSingularName()
    {
        return $this->singularName;
    }

    /**
     * Gets the singularSlug.
     *
     * @return string
     */
    public function getSingularSlug()
    {
        return $this->singularSlug;
    }

    /**
     * Gets the fields.
     *
     * @return \Bolt\Core\Field\FieldCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Indicates whether the contenttype should be displayed on the dashboard
     *
     * @return boolean
     */
    public function getShowOnDashboard()
    {
        return $this->showOnDashboard;
    }

    /**
     * Gets the sort column.
     *
     * @return string
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Gets the default status.
     *
     * @return string
     */
    public function getDefaultStatus()
    {
        return $this->defaultStatus;
    }

    public function addTableTo($schema)
    {
        $table = $schema->createTable($this->getKey());

        foreach($this->getFields() as $field) {
            $field->addColumnTo($table);
        }
    }

    /**
     * Guesses the slug if none is given
     *
     * @return string
     */
    protected function guessSlug()
    {
        return $this->key;
    }

    /**
     * Guesses the singular name if none is given
     *
     * @return string
     */
    protected function guessSingularName()
    {
        return Str::singular($this->name);
    }

    /**
     * Guesses the singular slug if none is given
     *
     * @return string
     */
    protected function guessSingularSlug()
    {
        return $this->singularName;
    }

    /**
     * Gets the default sort column
     *
     * @return string
     */
    protected function getDefaultSort()
    {
        return 'id';
    }

    /**
     * Gets the default status
     *
     * @return
     */
    protected function getDefaultDefaultStatus()
    {
        return 'draft';
    }

    protected function getDefaultOptions()
    {
        return array(
            'record_template' => 'entry.twig',
            'listing_template' => 'listing.twig',
            'listing_records' => 10,
            'default_status' => 'publish',
            'sort' => 'datepublish DESC',
            'recordsperpage' => 10,
        );
    }

    /**
     * Validates the properties of the contenttype
     *
     * @return void
     */
    protected function validate()
    {
        $cleaned = preg_replace("/[^a-zA-Z0-9-_]+/", "", $this->key);

        if($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid ContentType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }

        if($this->fields->isEmpty()) {
            $this->app['notify']->error('Missing "fields" key in contenttype with key "'.$this->key.'"');
        }
    }

}
