<?php


namespace GroupCache;


use GroupCache\Cache\Cache;
use GroupCache\Cache\CacheStats;
use GroupCache\SingleFlight\FlightGroup;
use GroupCache\Types\Once;
use GroupCache\Types\Stats;

class Group
{

    /**
     * @var string
     */
    private $name;
    /**
     * @var GetterInterface
     */
    private $getter;
    /**
     * @var float
     */
    private $cacheBytes;

    /**
     * @var Peers
     */
    private $peers;
    /**
     * @var FlightGroup
     */
    private $loadGroup;
    /**
     * @var Cache
     */
    private $hotCache;
    /**
     * @var Cache
     */
    private $mainCache;
    /**
     * @var Once
     */
    private $peersOnce;
    /**
     * @var Stats
     */
    private $stats;

    public function __construct(string $name, GetterInterface $getter, float $cacheBytes, Peers $peers, FlightGroup $loadGroup)
    {
        $this->name = $name;
        $this->getter = $getter;
        $this->peers = $peers;
        $this->cacheBytes = $cacheBytes;
        $this->hotCache = new Cache();
        $this->mainCache = new Cache();
        $this->peersOnce = new Once();
        $this->stats = new Stats();
        $this->loadGroup = $loadGroup;
    }


    public const MainCache = 1;
    public const HotCache = 2;
    public function cacheStats(int $which)
    {
        switch ($which) {
            case self::MainCache:
                return $this->mainCache->stats();
            case self::HotCache:
                return $this->hotCache->stats();
            default:
                return new CacheStats();
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function initPeers(): void
    {
        if ($this->peers === null) {
            $this->peers = getPeers($this->name);
        }
    }


    public function get(string $key)
    {
        $this->peersOnce->do([$this, 'initPeers']);
        $this->stats->gets->add();

        $value = $this->lookupCache($key);
        if ( $value !== null ) {
            $this->stats->cacheHits->add();
        }
        $destPopulated = false;
        $value = $this->load($key);
        if ($value === null || $destPopulated) {
            return null;
        }

        return $value;

    }

    public function load(string $key)
    {
        $this->stats->loads->add();
        $this->loadGroup->do($key, function () use($key) {
           $value = $this->lookupCache($key);
           if ( $value !== '' ) {
               $this->stats->cacheHits->add();
               return $value;
           }
           $this->stats->loadsDeduped->add();
           $peer = $this->peers->pickPeer($key);
           if ( $peer !== null ) {
               $value = $this->getFromPeer($peer, $key);
               if ($value !== "") {
                   $this->stats->peerLoads->add();
                   return $value;
               }
               $this->stats->peerErrors->add();
           }
           $value = $this->getLocally($key);
           if ( $value === "") {
               $this->stats->localLoadErrs->add();
               return $value;
           }
           $this->stats->localLoads->add();
           $this->populateCache($key, $value, $this->mainCache);
           return $value;
        });
    }

    public function populateCache($key, $value, Cache $cache): void
    {
        if ( $this->cacheBytes <= 0 ) {
            return;
        }
        $cache->add($key, $value);

        // Evict items form cache(s) if necessary
        for (;;) {
            $mainBytes = $this->mainCache->bytes();
            $hotBytes = $this->hotCache->bytes();
            if ($mainBytes+$hotBytes <= $this->cacheBytes) {
                return;
            }
            $victim = &$this->mainCache;
            if ($hotBytes > $mainBytes/8 ) {
                $victim = &$this->hotCache;
            }
            $victim->removeOldest();
        }
    }

    public function getLocally(string $key)
    {
        return $this->getter->get($key);
    }

    public function getFromPeer(Peer $peer, $key): string
    {
        return "";
    }

    public function lookupCache(string $key): string
    {
        if ( $this->cacheBytes < 0 ) {
            return '';
        }
        $value = $this->mainCache->get($key);
        return $value ?? $this->hotCache->get($key);
    }
}