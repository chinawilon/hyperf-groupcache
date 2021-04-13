<?php


namespace GroupCache\Cache;


class CacheStats
{
    public $bytes;
    public $items;
    public $gets;
    public $hits;
    public $evictions;

    public function __construct($nBytes=0, $items=0, $nGets=0, $nHits=0, $nEvict=0)
    {
        $this->bytes = $nBytes;
        $this->items= $items;
        $this->gets = $nGets;
        $this->hits = $nHits;
        $this->evictions = $nEvict;
    }
}