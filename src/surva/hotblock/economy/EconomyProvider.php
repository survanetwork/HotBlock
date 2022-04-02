<?php

/**
 * HotBlock | general economy provider
 */

namespace surva\hotblock\economy;

use pocketmine\player\Player;

interface EconomyProvider
{
    /**
     * Pay a specific amount of money to a player
     *
     * @param  \pocketmine\player\Player  $pl
     * @param  int  $moneyAmount
     */
    public function pay(Player $pl, int $moneyAmount): bool;

    /**
     * Get the account balance of a player
     *
     * @param  \pocketmine\player\Player  $pl
     *
     * @return int|null
     */
    public function get(Player $pl): ?int;
}
