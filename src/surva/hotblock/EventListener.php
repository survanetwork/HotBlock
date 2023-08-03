<?php

/**
 * HotBlock | event listener
 */

namespace surva\hotblock;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;

class EventListener implements Listener
{
    private HotBlock $hotBlock;

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

        $world       = $entity->getWorld();
        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if ($world->getFolderName() === $hbWorldName && $this->hotBlock->isInGameArea($entity)) {
            $blockUnder = $world->getBlock($entity->getPosition()->floor()->subtract(0, 1, 0));

            if ($blockUnder->hasSameTypeId(VanillaBlocks::OAK_PLANKS())) {
                $event->cancel();
            }
        }
    }
}
