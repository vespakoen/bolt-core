<?php

namespace Bolt\Core\Serializer;

use Layla\Cody\Blueprints\Package;
use Layla\Cody\Blueprints\Resource;

class CodySerializer
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function serialize()
    {
        $package = $this->getPackage();
        foreach ($this->app['contenttypes'] as $contentType) {
            $generatedModel = $this->getGeneratedModelForContentType($contentType);
            $package->addResource($generatedModel);

            // $model = $this->getModelForContentType($contentType);
            // $package->addResource($model);

            $repository = $this->getRepositoryForContentType($contentType);
            $package->addResource($repository);
        }

        return $package;
    }

    protected function getPackage()
    {
        $config = $this->app['config'];

        $vendor = $config->get('app/package/vendor', 'MyApp');
        $name = $config->get('app/package/name', 'Domain');

        return new Package($vendor, $name);
    }

    protected function getGeneratedModelForContentType($contentType)
    {
        $package = $this->getPackage();

        $type = 'model';

        $name = $this->getGeneratedModelNameForContentType($contentType);

        $configuration = array(
            'base' => 'Bolt.Core.Content.Eloquent.Model',
            'table' => $contentType->getKey(),
            'columns' => $this->getModelColumns($contentType)
        );

        $compilers = array(
            'php-laravel'
        );

        return new Resource($package, $type, $name, $configuration, $compilers);
    }

    protected function getGeneratedModelNameForContentType($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Models',
            'Generated',
            ucfirst($contentType->getSingularSlug())
        );

        return implode('.', $parts);
    }

    protected function getModelColumns($contentType)
    {
        $columns = array();

        foreach ($contentType->getFields() as $field) {
            $config = $field->getType()->getMigratorConfig();

            $columns[$field->getKey()] = array_merge(array(
                'type' => $config['type']
            ), $config['options']);
        }

        return $columns;
    }

    protected function getRepositoryForContentType($contentType)
    {
        $package = $this->getPackage();

        $type = 'class';

        $name = $this->getRepositoryNameForContentType($contentType);

        $configuration = array(
            'base' => 'Bolt.Core.Content.Eloquent.Repository',
            'uses' => array(
                'Bolt\Core\Support\Facades\Content',
                'Bolt\Core\Support\Facades\ContentCollection',
            ),
            'methods' => array(
                '__construct' => array(
                    'returnType' => 'void',
                    'parameters' => array(
                        'model' => array(
                            'type' => 'mixed',
                            'comment' => 'The Model to be used'
                        ),
                        'contentType' => array(
                            'type' => 'mixed',
                            'comment' => 'The ContentType configuration object'
                        )
                    ),
                    'content' => array(
                        'php-core' => '$this->model = $model;'."\n".
                            '$this->contentType = $contentType;'
                    )
                ),
                'getForListing' => array(
                    'returnType' => 'Bolt.Core.Content.ContentCollection',
                    'parameters' => array(
                        'options' => array(
                            'type' => 'array',
                            'comment' => 'Options for retrieving the content'
                        ),
                    ),
                    'content' => array(
                        'php-core' => '$models = $this->model->get();'."\n\n".
                            '$items = array();'."\n".
                            'foreach ($models as $model) {'."\n\t".
                                '$items[] = Content::create($this->contentType, $model, \'eloquent\');'."\n".
                            '}'."\n\n".
                            'return ContentCollection::create($models->all());'
                    )
                )
            )
        );

        $compilers = array(
            'php-core'
        );

        return new Resource($package, $type, $name, $configuration, $compilers);
    }

    protected function getRepositoryNameForContentType($contentType)
    {
        $package = $this->getPackage();

        $parts = array(
            $package->getVendor(),
            $package->getName(),
            'Repositories',
            'Generated',
            ucfirst($contentType->getSingularSlug()).'Repository'
        );

        return implode('.', $parts);
    }

}
