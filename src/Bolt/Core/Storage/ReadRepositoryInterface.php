<?php

namespace Bolt\Core\Storage;

use Bolt\Core\Config\Object\ContentType;

interface ReadRepositoryInterface {

    public function get($wheres = array(), $sort = null, $order = 'asc', $offset = 0, $limit = 10, $search = null);

    public function find($id);

}
