<?php

namespace Bolt\Core\Providers\Silex;

use Bolt\Core\Relation\Factory\RelationCollection;
use Bolt\Core\Relation\Factory\Relation;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RelationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerRelationFactories($app);
    }

    protected function registerRelationFactories(Application $app)
    {
        $app['relation.factory'] = $app->share(function ($app) {
            return new Relation($app);
        });

        $app['relations.factory'] = $app->share(function ($app) {
            return new RelationCollection($app);
        });
    }

    public function boot(Application $app)
    {
    }

}
