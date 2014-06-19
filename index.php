<?php

$paths = array(
    'app' => __DIR__.'/../app',
    'public' => __DIR__.'/../public',
    'base' => __DIR__.'/..',
    'storage' => __DIR__.'/../app/storage',
    'vendor' => __DIR__.'/../vendor'
);

require $paths['vendor'].'/autoload.php';

if ( ! isset($_SERVER['BOLT_ENV'])) {
    $_SERVER['BOLT_ENV'] = 'development';
}

$app = new Bolt\Core\App(array(
    'debug' => true,
    'env' => $_SERVER['BOLT_ENV'],
    'paths' => $paths,
    'locale' => 'nl',
    'config.files' => array(
        'app',
        'contenttypes',
        'defaultfields',
        'fieldtypes'
    ),
));

$needsRecompile = $app['env'] == "development";
if ($needsRecompile) {
    $app['compiler.cody.laravel']->compile();
    $app['compiler.cody.elasticsearch']->compile();
} else {
    $app['compiler.cody.laravel']->register();
    $app['compiler.cody.elasticsearch']->register();
}

$app->register(new Bolt\Provider\Silex\ControllerProvider);
$app->register(new Bolt\Provider\Silex\AuthProvider);

$app->boot();

$app['twig']->addGlobal('paths', $app['paths']);
$app['twig']->addExtension(new \Bolt\TwigExtension($app));

$app->mount('/', $app['controller.frontend']);
$app->mount($app['config']->get('app/branding/path'), $app['controller.admin']);
$app->mount($app['config']->get('app/branding/path').'/async', $app['controller.async']);

// maybe add some custom types
use Doctrine\DBAL\Types\Type;
Type::addType('point', 'CrEOF\Spatial\DBAL\Types\Geometry\PointType');
Type::addType('linestring', 'CrEOF\Spatial\DBAL\Types\Geometry\LineStringType');
Type::addType('geometry', 'CrEOF\Spatial\DBAL\Types\GeometryType');

$app->run();
