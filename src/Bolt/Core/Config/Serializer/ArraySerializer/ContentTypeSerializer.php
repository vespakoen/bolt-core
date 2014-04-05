<?php

namespace Bolt\Core\Config\Serializer\ArraySerializer;

class ContentTypeSerializer {

	public function serialize($contentType)
	{
		return array(
		    'key' => $contentType->getKey(),
		    'name' => $contentType->getName(),
		    'slug' => $contentType->getSlug(),
		    'singular_name' => $contentType->getSingularName(),
		    'singular_slug' => $contentType->getSingularSlug(),
		    'fields' => $contentType->getFields()->toArray(),
		    'show_on_dashboard' => $contentType->getShowOnDashboard(),
		    'sort' => $contentType->getSort(),
		    'default_status' => $contentType->getDefaultStatus(),
		);
	}

}
