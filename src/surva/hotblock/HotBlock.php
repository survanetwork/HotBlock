<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:01
 */

namespace surva\hotblock;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use surva\hotblock\tasks\PlayerCoinGiveTask;

class HotBlock extends PluginBase {
    /* @var Config */
    private $messages;

    /* @var EconomyAPI */
    private $economy;

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->messages = new Config($this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml");

        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerBlockCheckTask($this), $this->getConfig()->get("checkspeed", 0.25) * 20);
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new PlayerCoinGiveTask($this), $this->getConfig()->get("coinspeed", 0.25) * 20);
    }

    /**
     * Get a translated message
     *
     * @param string $key
     * @param array $replaces
     * @return string
     */
    public function getMessage(string $key, array $replaces = array()): string {
        if($rawMessage = $this->getMessages()->getNested($key)) {
            if(is_array($replaces)) {
                foreach($replaces as $replace => $value) {
                    $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
                }
            }
            return $rawMessage;
        }
        return $key;
    }

    /**
     * @return EconomyAPI
     */
    public function getEconomy(): EconomyAPI {
        return $this->economy;
    }

    /**
     * @return Config
     */
    public function getMessages(): Config {
        return $this->messages;
    }
}
