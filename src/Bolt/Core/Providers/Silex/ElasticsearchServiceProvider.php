<?php

namespace Bolt\Core\Providers\Silex;

use Elasticsearch\Client;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ElasticsearchServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['elasticsearch.options'] = array();

        $app['elasticsearch'] = $app->share(function($app) {
            return new Client($app['elasticsearch.options']);
        });
    }

    public function boot(Application $app)
    {
    }

}
