<?php


namespace GroupCache\Cache;

use GroupCache\LRU\Cache as LRUCache;

class Cache
{
    /**
     * @var int
     */
    private $nBytes = 0;
    /**
     * @var LRUCache
     */
    private $lru;

    private $nGet = 0;
    private $nHit = 0;
    private $nEvict = 0;

    public function __construct(?LRUCache $lru = null)
    {
        $this->lru = $lru;
    }

    public function stats(): CacheStats
    {
        return new CacheStats(
            $this->nBytes,
            $this->itemsLocked(),
            $this->nGet,
            $this->nHit,
            $this->nEvict,
        );
    }

    public function add(string $key, string $value): void
    {
        if ( $this->lru === null ) {
            $this->lru = new LRUCache(0, function ($key, $value){
                $this->nBytes -= strlen($key) + strlen($value);
                $this->nEvict++;
            });
        }
        $this->lru->add($key, $value);
        $this->nBytes += strlen($key) + strlen($value);
    }

    public function get(string $key): ?string
    {
        $this->nGet++;
        if ( is_null($this->lru) ) {
            return null;
        }
        $value = $this->lru->get($key);
        if ($value) {
            $this->nHit++;
        }
        return $value;
    }

    public function bytes(): int
    {
        return $this->nBytes;
    }

    public function items(): int
    {
        if ( is_null($this->lru) ) {
            return  0 ;
        }
        return $this->lru->len();
    }

    public function itemsLocked(): int
    {
        return $this->items();
    }

    public function removeOldest(): void
    {
        if ( $this->lru !== null ) {
            $this->lru->removeOldest();
        }
    }
}