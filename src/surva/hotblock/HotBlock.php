<?php

/**
 * HotBlock | plugin main class
 */

namespace surva\hotblock;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\hotblock\economy\CapitalProvider;
use surva\hotblock\economy\EconomyAPIProvider;
use surva\hotblock\economy\EconomyProvider;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use surva\hotblock\tasks\PlayerCoinGiveTask;

class HotBlock extends PluginBase
{
    private Config $defaultMessages;

    private Config $messages;

    private ?EconomyProvider $economyProvider = null;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->defaultMessages = new Config($this->getFile() . "resources/languages/en.yml");
        $this->messages        = new Config(
            $this->getFile() . "resources/languages/" . $this->getConfig()->get("language", "en") . ".yml"
        );

        $this->findEconomyPlugin();

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
     * Find a loaded economy plugin and set the provider
     */
    private function findEconomyPlugin(): void
    {
        if ($this->getServer()->getPluginManager()->getPlugin(EconomyAPIProvider::PLUGIN_NAME) !== null) {
            $this->economyProvider = new EconomyAPIProvider();
        } elseif ($this->getServer()->getPluginManager()->getPlugin(CapitalProvider::PLUGIN_NAME) !== null) {
            $this->economyProvider = new CapitalProvider($this->getConfig());
        }
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
        if (($rawMessage = $this->messages->getNested($key)) === null) {
            $rawMessage = $this->defaultMessages->getNested($key);
        }

        if ($rawMessage === null) {
            return $key;
        }

        foreach ($replaces as $replace => $value) {
            $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
        }

        return $rawMessage;
    }

    /**
     * @return \surva\hotblock\economy\EconomyProvider|null
     */
    public function getEconomyProvider(): ?EconomyProvider
    {
        return $this->economyProvider;
    }
}
