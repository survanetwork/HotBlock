<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:01
 */

namespace jjplaying\HotBlock;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;

class HotBlock extends PluginBase {
    private $economy;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }

    /**
     * @return EconomyAPI
     */
    public function getEconomy(): EconomyAPI {
        return $this->economy;
    }
}
