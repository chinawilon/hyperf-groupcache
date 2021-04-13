<?php


namespace GroupCache\Command;

use GroupCache\SingleFlight\Manager;
use GroupCache\SingleFlight\SingleFlight;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

/**
 * Class Spider
 * @Command()
 * @package Spider\Command
 */
class GroupCache extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('groupcache:run');
    }

    public function configure() : void
    {
        parent::configure();
        $this->setDescription('groupcache!!');
    }

    public function handle() : void
    {
        $s = new SingleFlight();
        for ($i=0; $i<10; $i++) {
            go(function() use($s) {
                echo $s->do('abc', static function(){
                    sleep(5);
                    return 'ok'.PHP_EOL;
                });
            });
        }
    }
}