<?php

namespace Bolt\Core\Content;

use Bolt\Core\ContentType\ContentType;

interface WriteRepositoryInterface {

    public function store($attributes);

    public function update($id, $attributes);

    public function delete($id);

}
