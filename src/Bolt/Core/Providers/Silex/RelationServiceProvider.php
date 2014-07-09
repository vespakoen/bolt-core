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
    }

    public function boot(Application $app)
    {
    }

}
