<?php

/**
 * HotBlock | plugin main class
 */

namespace surva\hotblock;

use DirectoryIterator;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use surva\hotblock\economy\BedrockEconomyProvider;
use surva\hotblock\economy\CapitalProvider;
use surva\hotblock\economy\EconomyAPIProvider;
use surva\hotblock\economy\EconomyProvider;
use surva\hotblock\tasks\PlayerBlockCheckTask;
use surva\hotblock\tasks\PlayerCoinGiveTask;
use surva\hotblock\utils\Messages;
use Symfony\Component\Filesystem\Path;

class HotBlock extends PluginBase
{
    /**
     * @var \pocketmine\utils\Config default language config
     */
    private Config $defaultMessages;

    /**
     * @var array available language configs
     */
    private array $translationMessages;

    private ?EconomyProvider $economyProvider = null;

    /**
     * Plugin has been enabled, initial setup
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();

        $this->saveResource(Path::join("languages", "en.yml"), true);
        $this->defaultMessages = new Config(Path::join($this->getDataFolder(), "languages", "en.yml"));
        $this->loadLanguageFiles();

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
        } elseif ($this->getServer()->getPluginManager()->getPlugin(BedrockEconomyProvider::PLUGIN_NAME) !== null) {
            $this->economyProvider = new BedrockEconomyProvider();
        }
    }

    /**
     * Check if a player is inside the game area
     *
     * @param  \pocketmine\player\Player  $pl
     *
     * @return bool
     */
    public function isInGameArea(Player $pl): bool
    {
        $conf = $this->getConfig();

        if (!$conf->exists("area")) {
            return true;
        }

        $ax = $conf->getNested("area.pos1.x");
        $ay = $conf->getNested("area.pos1.y");
        $az = $conf->getNested("area.pos1.z");

        $bx = $conf->getNested("area.pos2.x");
        $by = $conf->getNested("area.pos2.y");
        $bz = $conf->getNested("area.pos2.z");

        $px = $pl->getPosition()->getX();
        $py = $pl->getPosition()->getY();
        $pz = $pl->getPosition()->getZ();

        if ($bx > $ax) {
            if ($px < $ax || $px > $bx) {
                return false;
            }
        } elseif ($px > $ax || $px < $bx) {
                return false;
        }

        if ($by > $ay) {
            if ($py < $ay || $py > $by) {
                return false;
            }
        } elseif ($py > $ay || $py < $by) {
            return false;
        }

        if ($bz > $az) {
            if ($pz < $az || $pz > $bz) {
                return false;
            }
        } elseif ($pz > $az || $pz < $bz) {
            return false;
        }

        return true;
    }

    /**
     * Shorthand to send a translated message to a command sender
     *
     * @param  \pocketmine\command\CommandSender  $sender
     * @param  string  $key
     * @param  array  $replaces
     *
     * @return void
     */
    public function sendMessage(CommandSender $sender, string $key, array $replaces = []): void
    {
        $messages = new Messages($this, $sender);

        $sender->sendMessage($messages->getMessage($key, $replaces));
    }

    /**
     * Load all available language files
     *
     * @return void
     */
    private function loadLanguageFiles(): void
    {
        $resources = $this->getResources();

        foreach ($resources as $resource) {
            if (!preg_match("/languages\/[a-z]{2}.yml$/", $resource->getPathname())) {
                continue;
            }

            preg_match("/^[a-z][a-z]/", $resource->getFilename(), $fileNameRes);

            if (!isset($fileNameRes[0])) {
                continue;
            }

            $langId = $fileNameRes[0];

            $this->saveResource(Path::join("languages", $langId . ".yml"), true);
            $this->translationMessages[$langId] = new Config(
                Path::join($this->getDataFolder(), "languages", $langId . ".yml")
            );
        }
    }

    /**
     * @return array
     */
    public function getTranslationMessages(): array
    {
        return $this->translationMessages;
    }

    /**
     * @return \pocketmine\utils\Config
     */
    public function getDefaultMessages(): Config
    {
        return $this->defaultMessages;
    }

    /**
     * @return \surva\hotblock\economy\EconomyProvider|null
     */
    public function getEconomyProvider(): ?EconomyProvider
    {
        return $this->economyProvider;
    }
}
