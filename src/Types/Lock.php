<?php


namespace GroupCache\Types;


use Swoole\Coroutine\Channel;

class Lock
{
    /**
     * @var Channel
     */
    private $lock;

    public function __construct()
    {
        $this->lock = new Channel();
    }

    public function lock(): void
    {
        $this->lock->push(true);
    }

    public function unlock(): void
    {
        $this->lock->pop(true);
    }
}