<?php

namespace Bolt\Core\Provider\Silex;

use Bolt\Core\Content\Elasticsearch\ElasticsearchManager;

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

        $app['elasticsearch.manager'] = $app->share(function($app) {
            return new ElasticsearchManager($app, $app['elasticsearch']);
        });
    }

    public function boot(Application $app)
    {
    }

}
