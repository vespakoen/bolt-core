<?php

namespace Bolt\Core\Content;

use Bolt\Core\ContentType\ContentType;

interface ReadRepositoryInterface {

    public function get($wheres = array(), $sort = null, $order = 'asc', $offset = 0, $limit = 10, $search = null);

    public function find($id);

}
