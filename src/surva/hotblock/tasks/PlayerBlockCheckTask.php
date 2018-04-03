<?php
/**
 * Created by PhpStorm.
 * User: jarne
 * Date: 01.04.18
 * Time: 21:18
 */

namespace surva\hotblock\tasks;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\scheduler\PluginTask;
use surva\hotblock\HotBlock;

class PlayerBlockCheckTask extends PluginTask {
    /* @var HotBlock */
    private $hotBlock;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;

        parent::__construct($hotBlock);
    }

    public function onRun(int $currentTick) {
        if(!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName(
            $this->getHotBlock()->getConfig()->get("world", "world")
        ))) {
            return;
        }

        foreach($gameLevel->getPlayers() as $playerInLevel) {
            $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

            switch($blockUnderPlayer->getId()) {
                case Block::PLANKS:
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.safe"));
                    break;
                case Block::END_STONE:
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.run"));
                    break;
                case Block::NETHERRACK:
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("ground.poisoned"));

                    $effect = Effect::getEffectByName($this->getHotBlock()->getConfig()->get("effecttype", "POISON"));
                    $duration = $this->getHotBlock()->getConfig()->get("effectduration", 3) * 20;

                    $playerInLevel->addEffect(new EffectInstance($effect, $duration));
                    break;
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
