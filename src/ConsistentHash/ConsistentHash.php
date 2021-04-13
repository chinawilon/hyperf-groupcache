<?php

namespace GroupCache\ConsistentHash;

class ConsistentHash
{

    /**
     * @var int
     */
    private $replicas;

    /**
     * @var array
     */
    private $keys = [];

    /**
     * @var array
     */
    private $hashMap = [];

    /**
     * @var callable
     */
    private $fn;

    public function __construct(int $replicas, ?callable $fn)
    {
        $this->replicas = $replicas;
        // Warning: compatibility issue
        // $this->fn = $fn ?? 'crc32';
        $this->fn = $fn;
        if ( $fn === null ) {
            $this->fn = 'crc32';
        }
    }

    public function isEmpty(): bool
    {
        return count($this->keys) === 0;
    }

    public function add(string ...$keys): void
    {
        foreach ( $keys as $key ) {
            for ($i = 0; $i < $this->replicas; $i++) {
                $hash = ($this->fn)($i.$key);
                $this->keys[] = $hash;
                $this->hashMap[$hash] = $key;
            }
        }
        sort($this->keys);
    }

    public function get(string $key): string
    {
        if ( $this->isEmpty() ) {
            return '';
        }
        $hash = ($this->fn)($key);
        $idx = 0;
        foreach( $this->keys as $k ) {
            if ($k >= $hash) {
                $idx = $k; // find the first nearest node
                break;
            }
        }
        return $this->hashMap[$this->keys[$idx]];
    }
}