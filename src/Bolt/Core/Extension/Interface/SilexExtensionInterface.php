<?php

namespace Bolt\Core\Extension\Interface;

use Silex\Application;

interface SilexExtensionInterface
{

    public function registerSilexProviders(Application $app);

}
