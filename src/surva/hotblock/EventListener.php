<?php
/**
 * HotBlock | event listener
 */

namespace surva\hotblock;

use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {
    /* @var HotBlock */
    private $hotBlock;

    /**
     * EventListener constructor
     *
     * @param HotBlock $hotBlock
     */
    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
    }

    /**
     * Cancel damage if the player is on wood planks
     *
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $blockUnder = $world->getBlock($entity->floor()->subtract(0, 1));

        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if($world->getName() === $hbWorldName) {
            if($blockUnder->getId() === Block::PLANKS) {
                $event->setCancelled();
            }
        }
    }
}
