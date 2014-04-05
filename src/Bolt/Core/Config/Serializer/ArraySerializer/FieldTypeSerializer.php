<?php

namespace Bolt\Core\Config\Serializer\ArraySerializer;

class FieldTypeSerializer {

	public function serialize($field)
	{
		return array(
            'doctrine_type' => $this->getDoctrineType(),
            'serializer' => $this->getSerializerClass(),
            'migrator' => $this->getMigratorConfig()
        );
	}

}
