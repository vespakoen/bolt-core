<?php

namespace Bolt\Core\Content\Eloquent;

use Bolt\Core\Content\Content;

class EloquentContent extends Content
{
    public function getAttribute($key)
    {
        return $this->model->$key;
    }

}
