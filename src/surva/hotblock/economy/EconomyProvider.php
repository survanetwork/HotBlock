<?php

/**
 * HotBlock | general economy provider for implementations of economy plugin API's
 * to extend
 */

namespace surva\hotblock\economy;

use pocketmine\player\Player;

interface EconomyProvider
{
    /**
     * Pay a specific amount of money to a player
     *
     * @param Player $pl
     * @param int $moneyAmount
     */
    public function pay(Player $pl, int $moneyAmount): bool;

    /**
     * Get the account balance of a player
     *
     * @param Player $pl
     *
     * @return int|null
     */
    public function get(Player $pl): ?int;
}
