<?php
/**
 * HotBlock | plugin main class
 */

namespace surva\hotblock;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use surva\hotblock\tasks\PlayerCoinGiveTask;

class HotBlock extends PluginBase
{

    private Config $messages;

    private EconomyAPI $economy;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->messages = new Config(
          $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getScheduler()->scheduleRepeatingTask(
          new PlayerBlockCheckTask($this),
          $this->getConfig()->get("checkspeed", 0.25) * 20
        );
        $this->getScheduler()->scheduleRepeatingTask(
          new PlayerCoinGiveTask($this),
          $this->getConfig()->get("coinspeed", 0.25) * 20
        );
    }

    /**
     * Get a translated message
     *
     * @param  string  $key
     * @param  array  $replaces
     *
     * @return string
     */
    public function getMessage(string $key, array $replaces = []): string
    {
        if ($rawMessage = $this->messages->getNested($key)) {
            if (is_array($replaces)) {
                foreach ($replaces as $replace => $value) {
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
    public function getEconomy(): EconomyAPI
    {
        return $this->economy;
    }

}
