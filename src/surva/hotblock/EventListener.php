<?php
/**
 * HotBlock | event listener
 */

namespace surva\hotblock;

use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EventListener implements Listener
{

    /* @var HotBlock */
    private $hotBlock;

    /**
     * EventListener constructor
     *
     * @param  HotBlock  $hotBlock
     */
    public function __construct(HotBlock $hotBlock)
    {
        $this->hotBlock = $hotBlock;
    }

    /**
     * Cancel damage if the player is on wood planks
     *
     * @param  EntityDamageEvent  $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void
    {
        $entity = $event->getEntity();

        if (!($entity instanceof Player)) {
            return;
        }

        $world       = $entity->getLevel();
        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if ($world->getName() === $hbWorldName) {
            $blockUnder = $world->getBlock($entity->floor()->subtract(0, 1));

            if ($blockUnder->getId() === Block::PLANKS) {
                $event->setCancelled();
            }
        }
    }

}
