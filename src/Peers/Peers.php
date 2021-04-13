<?php


namespace GroupCache;


use GroupCache\Peers\NoPeers;

class Peers
{
    protected static $portPicker;

    public function pickPeer(string $key): Peer
    {
        return new Peer();
    }

    public static function registerPeerPicker(callable $fn)
    {
        self::$portPicker = $fn;
    }

    public static function getPeers(string $name): PeerPickerInterface
    {
        if ( is_null(self::$portPicker) ) {
            return new NoPeers();
        }
        $pk = (self::$portPicker)($name);
        if ( is_null($pk) ) {
            return new NoPeers();
        }
        return $pk;
    }

    public static function registerPerGroupPeerPicker(callable $fn)
    {
        self::$portPicker = $fn;
    }
}