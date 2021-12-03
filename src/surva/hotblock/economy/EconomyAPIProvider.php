<?php
/**
 * HotBlock | provider for EconomyAPI plugin
 */

namespace surva\hotblock\economy;

use onebone\economyapi\EconomyAPI;
use pocketmine\player\Player;
use pocketmine\Server;

class EconomyAPIProvider implements EconomyProvider
{

    private ?EconomyAPI $api;

    /**
     * Obtain EconomyAPI plugin object
     */
    public function __construct()
    {
        $server = Server::getInstance();

        $this->api = $server->getPluginManager()->getPlugin("EconomyAPI");
    }

    /**
     * @inheritDoc
     */
    public function pay(Player $pl, int $moneyAmount): bool
    {
        if ($this->api === null) {
            return false;
        }

        $res = $this->api->addMoney($pl, 1, false, "HotBlock");

        return $res === EconomyAPI::RET_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public function get(Player $pl): ?int
    {
        $money = $this->api->myMoney($pl);

        if (is_float($money)) {
            return intval($money);
        }

        return null;
    }

}
