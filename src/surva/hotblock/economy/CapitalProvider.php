<?php

/**
 * HotBlock | provider for Capital plugin
 */

namespace surva\hotblock\economy;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use SOFe\Capital\Capital;
use SOFe\Capital\CapitalException;
use SOFe\Capital\LabelSet;
use SOFe\Capital\Schema\Complete;
use SOFe\InfoAPI\InfoAPI;
use SOFe\InfoAPI\PlayerInfo;

class CapitalProvider implements EconomyProvider
{
    private ?Complete $selector;

    /**
     * Register Capital plugin selector from config
     */
    public function __construct(Config $plConfig)
    {
        Capital::api("0.1.0", function (Capital $api) use ($plConfig) {
            $this->selector = $api->completeConfig($plConfig->get("capital_selector", null));
        });
    }

    /**
     * @inheritDoc
     */
    public function pay(Player $pl, int $moneyAmount): bool
    {
        $res = false;

        Capital::api("0.1.0", function (Capital $api) use ($pl, $moneyAmount, &$res) {
            try {
                yield from $api->addMoney(
                    "HotBlock",
                    $pl,
                    $this->selector,
                    $moneyAmount,
                    new LabelSet(["reason" => "jumping"])
                );

                $res = true;
            } catch (CapitalException $e) {
                $res = false;
            }
        });

        return $res;
    }

    /**
     * @inheritDoc
     */
    public function get(Player $pl): ?int
    {
        $balanceText = InfoAPI::resolve("{money}", new PlayerInfo($pl));

        if (!is_numeric($balanceText)) {
            return null;
        }

        return intval($balanceText);
    }
}
