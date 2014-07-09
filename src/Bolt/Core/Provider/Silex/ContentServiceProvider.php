<?php

namespace Bolt\Core\Provider\Silex;

use Bolt\Core\Content\Factory\Content;
use Bolt\Core\Content\Factory\ContentCollection;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ContentServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $this->registerContentFactories($app);
    }

    protected function registerContentFactories(Application $app)
    {
    }

    public function boot(Application $app)
    {
    }

}
