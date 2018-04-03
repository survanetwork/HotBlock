<?php
/**
 * Created by PhpStorm.
 * User: jarne
 * Date: 01.04.18
 * Time: 21:26
 */

namespace surva\hotblock\tasks;

use pocketmine\block\Block;
use pocketmine\scheduler\PluginTask;
use surva\hotblock\HotBlock;

class PlayerCoinGiveTask extends PluginTask {
    /* @var HotBlock */
    private $hotBlock;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;

        parent::__construct($hotBlock);
    }

    public function onRun(int $currentTick) {
        if(!($gameLevel = $this->getHotBlock()->getServer()->getLevelByName($this->getHotBlock()->getConfig()->get("world", "world")))) {
            return;
        }

        $playersOnBlock = 0;

        foreach($gameLevel->getPlayers() as $playerInLevel) {
            $blockUnderPlayer = $gameLevel->getBlock($playerInLevel->subtract(0, 0.5));

            if($blockUnderPlayer->getId() === Block::QUARTZ_BLOCK) {
                if(count($gameLevel->getPlayers()) < $this->getHotBlock()->getConfig()->get("players", 2)) {
                    $playerInLevel->sendTip($this->getHotBlock()->getMessage("block.lessplayers", array("count" => $this->getHotBlock()->getConfig()->get("players", 3))));
                } else {
                    if($this->getHotBlock()->getConfig()->get("onlyplayer", false) === true) {
                        $playersOnBlock++;

                        if($playersOnBlock === 1) {
                            $onlyPlayer = $playerInLevel;
                        }
                    } else {
                        $playerInLevel->sendTip($this->getHotBlock()->getMessage("block.move"));
                        $playerInLevel->sendTip($this->getHotBlock()->getMessage("block.coins", array("count" => $this->getHotBlock()->getEconomy()->myMoney($playerInLevel))));

                        $this->getHotBlock()->getEconomy()->addMoney($playerInLevel, 1, false, "HotBlock");
                    }
                }
            }
        }

        if($this->getHotBlock()->getConfig()->get("onlyplayer", false) === true) {
            if($playersOnBlock === 1) {
                $onlyPlayer->sendTip($this->getHotBlock()->getMessage("block.move"));
                $onlyPlayer->sendTip($this->getHotBlock()->getMessage("block.coins", array("count" => $this->getHotBlock()->getEconomy()->myMoney($onlyPlayer))));

                $this->getHotBlock()->getEconomy()->addMoney($onlyPlayer, 1, false, "HotBlock");
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