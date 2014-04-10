<?php

namespace Bolt\Core\Config\Loader;

use Illuminate\Support\Str;

class YamlSerializerLoader extends YamlConfigLoader
{
    public function supports($resource, $type = null)
    {
        return Str::endsWith($resource, 'serializers.yml');
    }

}
