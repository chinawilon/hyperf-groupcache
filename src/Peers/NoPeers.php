<?php


namespace GroupCache\Peers;


use GroupCache\PeerPickerInterface;

class NoPeers implements PeerPickerInterface
{
    public function pickPeer(string $key)
    {

    }
}