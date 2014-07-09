<?php

namespace Bolt\Core\Provider\Silex;

use Bolt\Core\Field\Factory\FieldCollection;
use Bolt\Core\Field\Factory\Field;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FieldServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerFieldFactories($app);
    }

    protected function registerFieldFactories(Application $app)
    {

    }

    public function boot(Application $app)
    {
    }

}
