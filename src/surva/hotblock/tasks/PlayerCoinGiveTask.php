<?php

/**
 * HotBlock | player coin giving task
 */

namespace surva\hotblock\tasks;

use pocketmine\block\BlockLegacyIds;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use surva\hotblock\HotBlock;

class PlayerCoinGiveTask extends Task
{
    private HotBlock $hotBlock;

    public function __construct(HotBlock $hotBlock)
    {
        $this->hotBlock = $hotBlock;
    }

    /**
     * Give coins to players standing on the HotBlock
     */
    public function onRun(): void
    {
        $hbWorldName = $this->hotBlock->getConfig()->get("world", "world");

        if (!($gameWorld = $this->hotBlock->getServer()->getWorldManager()->getWorldByName($hbWorldName))) {
            return;
        }

        $onlyOnePlayer = $this->hotBlock->getConfig()->get("onlyplayer", false);

        if ($onlyOnePlayer === true) {
            $playersOnBlock = 0;

            foreach ($gameWorld->getPlayers() as $playerInLevel) {
                if ($this->isPlayerOnHotBlock($gameWorld, $playerInLevel)) {
                    $playersOnBlock++;
                }
            }

            if ($playersOnBlock !== 1) {
                return;
            }
        }

        foreach ($gameWorld->getPlayers() as $playerInLevel) {
            if ($this->isPlayerOnHotBlock($gameWorld, $playerInLevel)) {
                $this->handlePlayer($gameWorld, $playerInLevel);
            }
        }
    }

    /**
     * Check if a player is standing on the HotBlock
     *
     * @param  \pocketmine\world\World  $gameWld
     * @param  \pocketmine\player\Player  $pl
     *
     * @return bool
     */
    private function isPlayerOnHotBlock(World $gameWld, Player $pl): bool
    {
        $blockUnder = $gameWld->getBlock($pl->getPosition()->subtract(0, 0.5, 0));

        return ($blockUnder->getId() === BlockLegacyIds::QUARTZ_BLOCK);
    }

    /**
     * Handle coin giving of a player
     *
     * @param  \pocketmine\world\World  $gameWld
     * @param  \pocketmine\player\Player  $pl
     */
    private function handlePlayer(World $gameWld, Player $pl): void
    {
        $minPlayers = $this->hotBlock->getConfig()->get("players", 3);

        if (count($gameWld->getPlayers()) < $minPlayers) {
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

        $ep = $this->hotBlock->getEconomyProvider();

        if ($ep === null) {
            return;
        }

        $balance = $ep->get($pl);

        if ($balance !== null) {
            $pl->sendTip(
                $this->hotBlock->getMessage(
                    "block.coins",
                    ["count" => $balance]
                )
            );
        }

        $ep->pay($pl, 1);
    }
}
