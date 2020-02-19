<?php

namespace ScrapeKit\ScrapeKit\Chrome;

class Tabs
{

    protected $chrome;
    protected $items;

    public function __construct($chrome, $tabs)
    {

        $this->chrome = $chrome;
        $this->items  = collect($tabs)->map(function ($tab) {
            if (! $tab instanceof Tab) {
                return new Tab($this->chrome, $tab);
            }

            return $tab;
        });
    }

    public function first()
    {
        return $this->items->first();
    }

    public function whereTitleContains($title)
    {
        return new static($this->chrome, $this->items->filter(function (Tab $tab) use ($title) {
            return strpos($tab->getTitle(), $title) !== false;
        }));
    }

    public function find($id)
    {
        return $this->items->filter(function (Tab $tab) use ($id) {
            return $tab->id() == $id;
        })->first();
    }

    public function pages()
    {
        return new static($this->chrome, $this->items->filter(function (Tab $tab) {
            return $tab->getType() == 'page';
        }));
    }

    public function new($url = null)
    {

        $act = 'new';
        if ($url) {
            $act .= '?url=' . $url;
        }

        return new Tab($this->chrome, $this->chrome->api($act));
    }
}
