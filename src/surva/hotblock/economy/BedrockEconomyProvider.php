<?php

/**
 * HotBlock | provider for BedrockEconomy plugin
 */

namespace surva\hotblock\economy;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use cooldogedev\BedrockEconomy\libs\cooldogedev\libSQL\context\ClosureContext;
use pocketmine\player\Player;

class BedrockEconomyProvider implements EconomyProvider
{
    public const PLUGIN_NAME = "BedrockEconomy";

    /**
     * @var array last pay results
     */
    private array $payResCache;

    /**
     * @var array last player balance results
     */
    private array $getResCache;

    /**
     * @inheritDoc
     */
    public function pay(Player $pl, int $moneyAmount): bool
    {
        if (!isset($this->payResCache[$pl->getId()])) {
            $this->payResCache[$pl->getId()] = false;
        }

        BedrockEconomyAPI::legacy()->addToPlayerBalance(
            $pl->getName(),
            $moneyAmount,
            ClosureContext::create(function (bool $wasUpdated) use ($pl) {
                $this->payResCache[$pl->getId()] = $wasUpdated;
            })
        );

        return $this->payResCache[$pl->getId()];
    }

    /**
     * @inheritDoc
     */
    public function get(Player $pl): ?int
    {
        if (!isset($this->getResCache[$pl->getId()])) {
            $this->getResCache[$pl->getId()] = null;
        }

        BedrockEconomyAPI::legacy()->getPlayerBalance(
            $pl->getName(),
            ClosureContext::create(function (?int $balance) use ($pl) {
                $this->getResCache[$pl->getId()] = $balance;
            })
        );

        return $this->getResCache[$pl->getId()];
    }
}
