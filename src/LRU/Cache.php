<?php


namespace GroupCache\LRU;


class Cache
{
    /**
     * @var int
     */
    protected $maxEntries;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @var callable
     */
    private $evicted;


    /**
     * Cache constructor.
     *
     * @param int $maxEntries
     * @param callable $onEvicted
     */
    public function __construct(int $maxEntries,  callable $onEvicted)
    {
        $this->maxEntries = $maxEntries;
        $this->evicted = $onEvicted;
    }

    /**
     * @param string $key
     * @param string $value
     */
    public function add(string $key, string $value): void
    {
        $this->cache = array_merge([ $key => $value ], $this->cache);
        if ($this->maxEntries !== 0 && count($this->cache) > $this->maxEntries) {
            $this->removeOldest();
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): ?string
    {
        $value = null;
        if ( isset($this->cache[$key]) ) {
            $value = $this->cache[$key];
            $this->cache = array_merge([ $key => $value ], $this->cache);
        }
        return $value;
    }

    public function remove(string $key): void
    {
        unset($this->cache[$key]);
    }

    public function removeOldest()
    {
        return array_pop($this->cache);
    }

    public function len(): int
    {
        return count($this->cache);
    }


    public function clear(): void
    {
        if ( $this->evicted !== null ) {
            foreach( $this->cache as $key => $value ) {
                ($this->evicted)($key, $value);
            }
        }
        $this->cache = [];
    }
}