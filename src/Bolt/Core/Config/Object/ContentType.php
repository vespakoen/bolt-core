<?php

namespace Bolt\Core\Config\Object;

use Twig_Error_Loader;

use Illuminate\Support\Contracts\ArrayableInterface;

use Bolt\Core\App;
use Bolt\Core\Config\Object\Content;
use Bolt\Core\Config\Object\Collection\ContentCollection;
use Bolt\Core\Config\Object\Collection\FieldCollection;
use Bolt\Core\Config\Object\Collection\RelationCollection;
use Bolt\Core\Config\ConfigObject;

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
     * The fields for this content type
     *
     * @var \Bolt\Core\Config\Object\Collection\FieldCollection
     */
    protected $fields;

    /**
     * The relations for this content type
     *
     * @var \Bolt\Core\Config\Object\Collection\RelationCollection
     */
    protected $relations;

    /**
     * The options
     *
     * @var array
     */
    protected $options;

    /**
     * Create a new ContentType instance
     *
     * @param $app
     * @param $key
     * @param $fields \Bolt\Core\Config\Object\Collection\FieldCollection
     * @param $relations \Bolt\Core\Config\Object\Collection\RelationCollection
     * @param $options array
     *
     * @return \Bolt\Core\Config\Object\ContentType
     */
    public function __construct($app, $key, FieldCollection $fields, RelationCollection $relations = null, $options = array())
    {
        $this->app = $app;
        $this->key = $key;
        $this->fields = $fields;
        $this->relations = $relations;
        $this->options = $options;

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
     * Gets the fields.
     *
     * @return \Bolt\Core\Field\FieldCollection
     */
    public function getFields()
    {
        return $this->fields;
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

    public function getAllFields()
    {
        return $this->getDefaultFields()
            ->merge($this->getFields());
    }

    public function getDatabaseFields()
    {
        return $this->getAllFields()
            ->getDatabaseFields();
    }

    public function getValidationFields()
    {
        return $this->getAllFields()
            ->getValidationFields();
    }

    public function getIdField()
    {
        $fields = $this->getDefaultFields();

        return $fields->forPurpose('id');
    }

    public function getTitleField()
    {
        if ($titleFieldKey = $this->get('title_field')) {
            return $this->fields->get($titleFieldKey);
        }

        $textFields = $this->getFields()->getTextFields();

        return $textFields->first();
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

    public function getSearchFields()
    {
        if ($searchFields = $this->get('search_fields')) {
            return $this->getFields()->filterByKeys($searchFields);
        }

        return $this->getFields()->getTextFields();
    }

    public function getDefaultFields()
    {
        $config = $this->app['config']->get('defaultfields');
        $fields = $this->app['fields.factory']->fromConfig($config);

        if ($this->get('sortable', false) == false) {
            $weightFieldKey = $fields->forPurpose('weight')->getKey();
            $fields->forget($weightFieldKey);
        }

        return $fields;
    }

    public function getDefaultSortField()
    {
        $defaultFields = $this->getDefaultFields();

        $weightField = $defaultFields->forPurpose('weight');

        if ($weightField) {
            return $weightField;
        }

        return $defaultFields->forPurpose('datechanged');
    }

    public function getDefaultOrder()
    {
        $defaultSortField = $this->getDefaultSortField();

        return $defaultSortField->get('purpose') == "weight" ? 'asc' : 'desc';
    }

    public function getRules()
    {
        $rules = array();

        $fields = $this->getValidationFields();

        foreach ($fields as $field) {
            $rules = array_merge($rules, $field->getRules());
        }

        return $rules;
    }

    public function validateInput($input)
    {
        $rules = $this->getRules();

        $validator = $this->app['illuminate']['validator']->make($input, $rules);
        if ($validator->fails()) {
            $flashBag = $this->app['session']->getFlashBag();
            $errors = $validator->errors()->getMessages();
            $flashBag->set('errors', $errors);
            $flashBag->set('input', $input);

            return false;
        }

        return true;
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

    public function getViewForForm(Content $content = null)
    {
        if (is_null($content)) {
            $content = $this->app['storage.service']->getNew($this);
        }

        $contentType = $this;
        $view = 'contenttypes/form/' . $contentType->getKey();

        $context = compact(
            'contentType',
            'content',
            'view'
        );

        return $this->app['view.factory']->create($view, $context);
    }

    public function getViewForListing(ContentCollection $contents = null)
    {
        if (is_null($contents)) {
            $contents = $this->app['contents.factory']->create(array(), $this);
        }

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
    }

    public function toArray()
    {
        return array_merge($this->options, array(
            'key' => $this->getKey(),
            'fields' => $this->getAllFields()->toArray(),
            'relations' => $this->getRelations()->toArray()
        ));
    }

}
