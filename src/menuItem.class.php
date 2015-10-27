<?php
namespace ipinga;

class menuItem
{
    public $name;
    public $url;
    public $target;

    public function __construct($name,$url,$target='')
    {
        $this->name = $name;
        $this->url  = $url;
        $this->target = $target;
    }
}