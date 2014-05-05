<?php

namespace Bolt\Core\ContentType;

use Twig_Error_Loader;

use Bolt\Core\App;
use Bolt\Core\Content\Content;
use Bolt\Core\Content\ContentCollection;
use Bolt\Core\Field\FieldCollection;
use Bolt\Core\Relation\RelationCollection;
use Bolt\Core\Config\ConfigObject;

use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Str;

/**
 * A ContentType defines a resource and can be used to build Forms and Listings
 */
class ContentType extends ConfigObject implements ArrayableInterface
{
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
    public function __construct($app, $key, $name, FieldCollection $fields = null, RelationCollection $relations = null, $slug = null, $singularName = null, $singularSlug = null, $showOnDashboard = null, $sort = null, $defaultStatus = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->name = $name;
        $this->fields = $fields;
        $this->relations = $relations;
        $this->slug = is_null($slug) ? $this->guessSlug() : $slug;
        $this->singularName = is_null($singularName) ? $this->guessSingularName() : $singularName;
        $this->singularSlug = is_null($singularSlug) ? $this->guessSingularSlug() : $singularSlug;
        $this->showOnDashboard = is_null($showOnDashboard) ? true : $showOnDashboard;
        $this->sort = is_null($sort) ? $this->getDefaultSort() : $sort;
        $this->defaultStatus = is_null($defaultStatus) ? $this->getDefaultDefaultStatus() : $defaultStatus;
        $this->options = array_merge($this->getDefaultOptions(), $options);

        $this->validate();
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

    public function getAllFields()
    {
        return $this->getDefaultFields()->merge($this->getFields());
    }

    public function getTitleField()
    {
        if ($titleFieldKey = $this->get('title_field')) {
            return $this->fields->get($titleFieldKey);
        }

        $titleGuessFields = $this->fields->filterByTypeKeys(array(
            'string',
            'text',
            'textarea',
            'html',
            'markdown'
        ));

        return $titleGuessFields->first();
    }

    public function getImageField()
    {
        if ($imageFieldKey = $this->get('image_field')) {
            return $this->fields->get($imageFieldKey);
        }

        $imageGuessFields = $this->fields->filterByTypeKeys(array(
            'image',
            'uploadcare'
        ));

        return $imageGuessFields->first();
    }

    public function getDefaultFields()
    {
        $config = $this->app['config']->get('defaultfields');

        return $this->app['fields.factory']->fromConfig($config);
    }

    /**
     * Gets the relations.
     *
     * @return \Bolt\Core\Relation\RelationCollection
     */
    public function getRelations()
    {
        return $this->relations;
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

    public function getTableName()
    {
        return $this->key;
    }

    public function addTableTo($schema)
    {
        $table = $schema->createTable($this->getTableName());

        $this->getAllFields()->addColumnsTo($table);
        $this->getRelations()->addColumnsTo($table);
    }

    public function toArray()
    {
        return array(
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'slug' => $this->getSlug(),
            'singular_name' => $this->getSingularName(),
            'singular_slug' => $this->getSingularSlug(),
            'fields' => $this->getFields()->toArray(),
            'show_on_dashboard' => $this->getShowOnDashboard(),
            'sort' => $this->getSort(),
            'default_status' => $this->getDefaultStatus(),
        );
    }

    public function getViewForForm(Content $content = null)
    {
        $contentType = $this;
        $view = 'contenttypes/form/' . $contentType->getKey();

        $context = compact(
            'contentType',
            'content',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function getViewForListing(ContentCollection $contents)
    {
        $contentType = $this;
        $view = 'contenttypes/listing/' . $contentType->getKey();

        $context = compact(
            'contentType',
            'contents',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
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
        return Str::slug($this->singularName);
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

        if ($this->key !== $cleaned) {
            $this->app['notify']->error(sprintf('Invalid ContentType key "%s". It may only contain [a-z, A-Z, 0-9, -, _].', $this->key));
        }

        if ($this->fields->isEmpty()) {
            $this->app['notify']->error('Missing "fields" key in contenttype with key "'.$this->key.'"');
        }
    }

}
