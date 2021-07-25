<?php
/**
 * HotBlock | player block checking task
 */

namespace surva\hotblock\tasks;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;

class PlayerBlockCheckTask extends Task
{

    /* @var HotBlock */
    private $hotBlock;

    /**
     * PlayerBlockCheckTask constructor
     *
     * @param  HotBlock  $hotBlock
     */
    public function __construct(HotBlock $hotBlock)
    {
        $this->hotBlock = $hotBlock;
    }

    /**
     * Task run
     *
     * @param  int  $currentTick
     */
    public function onRun(int $currentTick): void
    {
        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if (!($gameLevel = $this->hotBlock->getServer()->getLevelByName($hbWorldName))) {
            return;
        }

        foreach ($gameLevel->getPlayers() as $playerInLevel) {
            $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

            switch ($blockUnderPlayer->getId()) {
                case Block::PLANKS:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.safe"));
                    break;
                case Block::END_STONE:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.run"));
                    break;
                case Block::NETHERRACK:
                    $playerInLevel->sendTip($this->hotBlock->getMessage("ground.poisoned"));

                    $effect   = Effect::getEffectByName($this->hotBlock->getConfig()->get("effecttype", "POISON"));
                    $duration = $this->hotBlock->getConfig()->get("effectduration", 3) * 20;

                    $playerInLevel->addEffect(new EffectInstance($effect, $duration));
                    break;
            }
        }
    }

}
