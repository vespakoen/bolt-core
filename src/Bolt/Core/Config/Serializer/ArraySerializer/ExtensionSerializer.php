<?php

namespace Bolt\Core\Config\Serializer\ArraySerializer;

class ExtensionSerializer {

	public function serialize($extension)
	{
		return array(
		    'key' => $extension->getKey(),
		    'enabled' => $extension->getEnabled(),
		    'providers' => $extension->getProviders(),
		    'config' => $extension->getConfig(),
		);
	}

}
