<?php

namespace Bolt\Core\Storage;

use Bolt\Core\Config\Object\ContentType;

interface WriteRepositoryInterface {

    public function store($attributes);

    public function update($id, $attributes);

    public function delete($id);

}
