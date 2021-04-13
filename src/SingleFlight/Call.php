<?php


namespace GroupCache\SingleFlight;

use Swoole\Coroutine\Channel;

class Call
{
    /**
     * @var
     */
    private $result;

    /**
     * @var callable
     */
    private $fn;

    /**
     * @var Channel
     */
    private $chan;

    public function __construct(callable $fn)
    {
        $this->chan = new Channel();
        $this->fn = $fn;
    }

    public function wait(): void
    {
        $this->chan->pop();
    }

    public function getResult()
    {
        return $this->result;
    }

    public function do()
    {
        $this->result = ($this->fn)();
        $this->chan->close();
        return $this->result;
    }

}