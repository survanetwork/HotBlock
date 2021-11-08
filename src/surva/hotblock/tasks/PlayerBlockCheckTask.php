<?php
/**
 * HotBlock | player block checking task
 */

namespace surva\hotblock\tasks;

use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;

class PlayerBlockCheckTask extends Task
{

    private HotBlock $hotBlock;

    public function __construct(HotBlock $hotBlock)
    {
        $this->hotBlock = $hotBlock;
    }

    /**
     * Check if players are on specific blocks and send messages/effects
     */
    public function onRun(): void
    {
        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if (!($gameWorld = $this->hotBlock->getServer()->getWorldManager()->getWorldByName($hbWorldName))) {
            return;
        }

        foreach ($gameWorld->getPlayers() as $playerInLevel) {
            $blockUnderPlayer = $gameWorld->getBlock($playerInLevel->getPosition()->subtract(0, 0.5, 0));

            switch ($blockUnderPlayer->getId()) {
                case BlockLegacyIds::PLANKS:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.safe"));
                    break;
                case BlockLegacyIds::END_STONE:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.run"));
                    break;
                case BlockLegacyIds::NETHERRACK:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.poisoned"));

                    $effect   = VanillaEffects::fromString($this->hotBlock->getConfig()->get("effecttype", "POISON"));
                    $duration = $this->hotBlock->getConfig()->get("effectduration", 3) * 20;

                    $playerInLevel->getEffects()->add(new EffectInstance($effect, $duration));
                    break;
            }
        }
    }

}
