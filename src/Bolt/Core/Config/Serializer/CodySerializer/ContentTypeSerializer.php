<?php

namespace Bolt\Core\Config\Serializer\CodySerializer;

class ContentTypeSerializer {

	public function serialize($contentType)
	{
		return json_encode(array(
			$this->getRepositoryName($contentType) => array(
				'type' => 'class',
				'base' => 'Bolt.Core.Repositories.ContentRepository',
				'methods' => array(
					"__construct" => array(
						"parameters" => array(
							"model" => array(
								"type" => "mixed",
								"comment" => "The Model to be used"
							)
						)
					)
				)
			),
			$this->getModelName($contentType) => array(
				'type' => 'model',
				'base' => 'Bolt.Core.Models.Content',
				'table' => $contentType->getKey(),
				'columns' => $this->getModelColumns($contentType)
			)
		), JSON_PRETTY_PRINT);
	}

	protected function getRepositoryName($contentType)
 	{
 		return 'App.Domain.Repositories.'.ucfirst($contentType->getKey()).'Repository';
 	}

 	protected function getModelName($contentType)
 	{
 		return 'App.Domain.Models.'.ucfirst($contentType->getKey());
 	}

 	protected function getModelColumns($contentType)
 	{
 		$columns = array();

 		foreach($contentType->getFields() as $field) {
 			$config = $field->getType()->getMigratorConfig();

 			$columns[$field->getKey()] = array_merge(array(
 				'type' => $config['type']
 			), $config['options']);
 		}

 		return $columns;
 	}

}
