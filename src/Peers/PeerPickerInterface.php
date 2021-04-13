<?php


namespace GroupCache;


interface PeerPickerInterface
{
    public function pickPeer(string $key): ProtoGetterInterface;
}