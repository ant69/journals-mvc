<?php
namespace App;

class index
{
    private $index;
    function __construct()
    {
        $this->index = 'Initialized';
    }

    function Index()
    {
        echo $this->index;
    }
}