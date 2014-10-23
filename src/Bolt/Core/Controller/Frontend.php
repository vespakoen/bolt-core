<?php

namespace Bolt\Core\Controller;

use DateTime;

use Silex\Application;
use Silex\ControllerProviderInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Frontend extends Controller implements ControllerProviderInterface
{
    public function __construct($app)
    {
        $this->app = $app;
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'controller.frontend:getHome')->bind('home');
        $controllers->get('download/{namespace}', 'controller.frontend:getDownload')->bind('download');

        return $controllers;
    }

    public function getHome(Request $request, Application $app)
    {
        $expires = with(new \DateTime())->setTimestamp(strtotime('+1 day'));

        return Response::create($this->view('frontend/home'), 200)
            ->setExpires($expires);
    }

    public function getDownload(Request $request, Application $app, $namespace)
    {
        $map = array(
            'nl.trapps.vuursteenroute' => 'nltrappsvuursteenroute',
            'nl.trapps.fruitlijn' => 'nltrappsfruiteropuit',
            'nl.trapps.bronkroutegronsveld' => 'nltrappsgronsveld',
            'nl.trapps.voeren' => 'nltrappsvoeren'
        );

        if (array_key_exists($namespace, $map)) {
            $namespace = $map[$namespace];
        }

        $wheres = array(
            'apps.namespace' => $namespace
        );

        $project = $app['repository.eloquent.apps']->findBy($wheres, false);

        if ( ! $project) {
            return $app->abort(404, "Page not found.");
        }

        $notsupported_url = "http://www.trapps.nl";

        return $this->view('frontend/download', compact('project', 'notsupported_url'));
    }
}
