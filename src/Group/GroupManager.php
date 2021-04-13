<?php


namespace GroupCache\Group;


use GroupCache\Exceptions\GroupCacheException;
use GroupCache\GetterInterface;
use GroupCache\Group;
use GroupCache\Peers;
use GroupCache\SingleFlight\SingleFlight;
use GroupCache\Types\Once;

class GroupManager
{
    protected $groups = [];
    /**
     * @var \Closure
     */
    private $initPeerServer;
    /**
     * @var Once
     */
    private $once;
    /**
     * @var \Closure
     */
    private $newGroupHook;

    public function __construct()
    {
        $this->once = new Once();
        $this->initPeerServer = static function () {};
        $this->newGroupHook = static function() {};
    }

    /**
     * @param string $key
     * @return mixed
     * @throws GroupCacheException
     */
    public function getGroup(string $key)
    {
        if( isset($this->groups[$key]) ) {
            return $this->groups[$key];
        }
        throw New GroupCacheException("groupcache: group($key) not exists");
    }

    /**
     * @param string $name
     * @param $cacheBytes
     * @param GetterInterface $getter
     * @param Peers $peers
     * @return Group
     */
    public function newGroup(string $name, $cacheBytes, GetterInterface $getter, Peers $peers): Group
    {
        $this->once->do(function (){
            ($this->initPeerServer)();
        });

        $group = new Group($name, $getter, $cacheBytes, $peers, new SingleFlight());
        ($this->newGroupHook)();
        $this->groups[$name] = $group;
        return $group;
    }

    /**
     * @param callable $fn
     */
    public function registerNewGroupHook(callable $fn)
    {
        $this->newGroupHook = $fn;
    }

    /**
     * @param callable $fn
     */
    public function registerServerStart(callable $fn)
    {
        $this->initPeerServer = $fn;
    }

}