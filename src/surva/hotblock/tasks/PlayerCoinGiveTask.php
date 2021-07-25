<?php
/**
 * HotBlock | player coin giving task
 */

namespace surva\hotblock\tasks;

use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use surva\hotblock\HotBlock;

class PlayerCoinGiveTask extends Task
{

    /* @var HotBlock */
    private $hotBlock;

    /**
     * PlayerCoinGiveTask constructor
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

        $onlyOnePlayer = $this->hotBlock->getConfig()->get("onlyplayer", false);

        if ($onlyOnePlayer === true) {
            $playersOnBlock = 0;

            foreach ($gameLevel->getPlayers() as $playerInLevel) {
                if ($this->isPlayerOnHotBlock($gameLevel, $playerInLevel)) {
                    $playersOnBlock++;
                }
            }

            if ($playersOnBlock !== 1) {
                return;
            }
        }

        foreach ($gameLevel->getPlayers() as $playerInLevel) {
            if ($this->isPlayerOnHotBlock($gameLevel, $playerInLevel)) {
                $this->handlePlayer($gameLevel, $playerInLevel);
            }
        }
    }

    /**
     * Check if a player is standing on the HotBlock
     *
     * @param  \pocketmine\level\Level  $gameLvl
     * @param  \pocketmine\Player  $pl
     *
     * @return bool
     */
    private function isPlayerOnHotBlock(Level $gameLvl, Player $pl): bool
    {
        $blockUnder = $gameLvl->getBlock($pl->subtract(0, 0.5));

        return ($blockUnder->getId() === Block::QUARTZ_BLOCK);
    }

    /**
     * Handle coin giving of a player
     *
     * @param  Level  $gameLvl
     * @param  Player  $pl
     */
    private function handlePlayer(Level $gameLvl, Player $pl): void
    {
        $minPlayers = $this->hotBlock->getConfig()->get("players", 3);

        if (count($gameLvl->getPlayers()) < $minPlayers) {
            $pl->sendTip(
              $this->hotBlock->getMessage(
                "block.lessplayers",
                ["count" => $minPlayers]
              )
            );

            return;
        }

        $this->payCoins($pl);
    }

    /**
     * Pay the coins to a player
     *
     * @param  Player  $pl
     */
    private function payCoins(Player $pl): void
    {
        $pl->sendTip($this->hotBlock->getMessage("block.move"));
        $pl->sendTip(
          $this->hotBlock->getMessage(
            "block.coins",
            ["count" => $this->hotBlock->getEconomy()->myMoney($pl)]
          )
        );

        $this->hotBlock->getEconomy()->addMoney($pl, 1, false, "HotBlock");
    }

}
