<?php

namespace Bolt\Core\Content;

use Bolt\Core\ContentType\ContentType;

interface ReadRepositoryInterface {

    public function getForListing($sort, $order = 'asc', $offset = 0, $limit = 10, $search = null);

    public function find($id);

    public function findMany($ids);

}
