<?php


namespace GroupCache\SingleFlight;


class SingleFlight implements FlightGroup
{
    /**
     * @var array
     */
    protected $doing = [];

    /**
     * @param string $key
     * @param callable $fn
     * @return mixed
     */
    public function do(string $key, callable $fn)
    {
        if ( isset($this->doing[$key]) ) {
            /**@var $call Call **/
            $call = $this->doing[$key];
            $call->wait();
            return $call->getResult();
        }
        $call = new Call($fn);
        $this->doing[$key] = $call;
        $result = $call->do();
        unset($this->doing[$key]);
        return $result;
    }
}