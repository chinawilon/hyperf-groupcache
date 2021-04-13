<?php


namespace GroupCache\SingleFlight;


interface FlightGroup
{
    public function do(string $key, callable $fn);
}