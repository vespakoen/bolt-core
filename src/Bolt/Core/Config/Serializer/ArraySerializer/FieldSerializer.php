<?php

namespace Bolt\Core\Config\Serializer\ArraySerializer;

class FieldSerializer {

	public function serialize($field)
	{
		return array_merge(array(
		    'key' => $field->getKey(),
		    'type' => $field->getType()->getKey()
		), $field->getOptions());
	}

}
