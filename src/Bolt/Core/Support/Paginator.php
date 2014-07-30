<?php

namespace Bolt\Core\Support;

class Paginator {

    public function __construct($request, $page, $limit, $total)
    {
        $this->request = $request;
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function getShowingFrom()
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getShowingTo()
    {
        return $this->page * $this->limit;
    }

    public function hasPrevious()
    {
        return $this->page > 1;
    }

    public function hasNext()
    {
        $totalPages = $this->getTotalPages();

        return $this->page < $totalPages;
    }

    public function getTotalPages()
    {
        return ceil($this->total / $this->limit);
    }

    public function getPreviousParams()
    {
        return $this->getPageParams($this->page - 1);
    }

    public function getNextParams()
    {
        return $this->getPageParams($this->page + 1);
    }

    public function getPageParams($page)
    {
        return array_merge($this->request->query->all(), array(
            'page' => $page
        ));
    }

    public function getPreviousLink()
    {
        $qs = http_build_query($this->getPreviousParams());

        return $this->request->getBasePath().'?'.$qs;
    }

    public function getNextLink()
    {
        $qs = http_build_query($this->getNextParams());

        return $this->request->getBasePath().'?'.$qs;
    }

    public function getPageLink($page)
    {
        $qs = http_build_query($this->getPageParams($page));

        return $this->request->getBasePath().'?'.$qs;
    }

}
