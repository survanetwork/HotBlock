<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:02
 */

namespace surva\hotblock;

use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {
    /* @var HotBlock */
    private $hotBlock;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));

        if($world->getName() === $this->getHotBlock()->getConfig()->get("world", "world")) {
            if($block->getId() === Block::PLANKS) {
                $event->setCancelled();
            }
        }
    }

    /**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}
