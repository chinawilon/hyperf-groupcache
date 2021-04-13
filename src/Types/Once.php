<?php


namespace GroupCache\Types;


use Swoole\Atomic;

class Once
{

    /**
     * @var Atomic
     */
    private $atomic;


    public function __construct()
    {
        $this->atomic = new Atomic();
    }

    public function do(callable $fn): void
    {
        if ( $this->atomic->add() === 1) {
            $fn();
        }
    }
}