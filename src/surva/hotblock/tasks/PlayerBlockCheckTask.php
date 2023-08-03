<?php

/**
 * HotBlock | player block checking task
 */

namespace surva\hotblock\tasks;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;
use surva\hotblock\utils\Messages;

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
            if (!$this->hotBlock->isInGameArea($playerInLevel)) {
                continue;
            }

            $blockUnderPlayer = $gameWorld->getBlock($playerInLevel->getPosition()->subtract(0, 0.5, 0));

            $messages = new Messages($this->hotBlock, $playerInLevel);

            switch ($blockUnderPlayer->getTypeId()) {
                case VanillaBlocks::OAK_PLANKS()->getTypeId():
                    $playerInLevel->sendTip($messages->getMessage("ground.safe"));
                    break;
                case VanillaBlocks::END_STONE()->getTypeId():
                    $playerInLevel->sendTip($messages->getMessage("ground.run"));
                    break;
                case VanillaBlocks::NETHERRACK()->getTypeId():
                    $playerInLevel->sendTip($messages->getMessage("ground.poisoned"));

                    $effect = StringToEffectParser::getInstance()->parse(
                        $this->hotBlock->getConfig()->get("effecttype", "POISON")
                    );

                    if ($effect !== null) {
                        $duration = $this->hotBlock->getConfig()->get("effectduration", 3) * 20;

                        $playerInLevel->getEffects()->add(new EffectInstance($effect, $duration));
                    }
                    break;
            }
        }
    }
}
