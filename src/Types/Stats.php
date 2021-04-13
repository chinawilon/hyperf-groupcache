<?php


namespace GroupCache\Types;


use Swoole\Atomic;

class Stats
{

    /**
     * @var Atomic
     */
    public $gets;
    /**
     * @var Atomic
     */
    public $cacheHits;
    /**
     * @var Atomic
     */
    public $peerLoads;
    /**
     * @var Atomic
     */
    public $peerErrors;
    /**
     * @var Atomic
     */
    public $loads;
    /**
     * @var Atomic
     */
    public $loadsDeduped;
    /**
     * @var Atomic
     */
    public $localLoads;
    /**
     * @var Atomic
     */
    public $localLoadErrs;
    /**
     * @var Atomic
     */
    public $serverRequets;

    public function __construct()
    {
        $this->gets = new Atomic();
        $this->cacheHits = new Atomic();
        $this->peerLoads = new Atomic();
        $this->peerErrors = new Atomic();
        $this->loads = new Atomic();
        $this->loadsDeduped = new Atomic();
        $this->localLoads = new Atomic();
        $this->localLoadErrs = new Atomic();
        $this->serverRequets = new Atomic();
    }
}