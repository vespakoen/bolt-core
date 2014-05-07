<?php

namespace Bolt\Core\Content\Eloquent;

class Link extends Model
{
    protected $table = 'relations';

    public function linkable()
    {
        return $this->morphTo();
    }

    public function other()
    {
        return $this->morphTo('other', 'to_contenttype', 'to_id');
    }

}
