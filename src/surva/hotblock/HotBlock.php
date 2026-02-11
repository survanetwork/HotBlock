<?php

/**
 * HotBlock | plugin main class, initialize economy provider and events/tasks,
 * provide functions to check game area
 */

namespace surva\hotblock;

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
     * @var Config default language config
     */
    private Config $defaultMessages;
    /**
     * @var Config[] available language configs
     */
    private array $translationMessages;
    private ?EconomyProvider $economyProvider = null;

    /**
     * Initialize plugin, load language files, find economy provider plugin, and register
     * events and tasks
     */
    public function onEnable(): void
    {
        $this->defaultMessages = new Config($this->getResourcePath(Path::join("languages", "en.yml")));
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
     * Find an economy plugin on the server and set
     * it as the economy provider
     *
     * @return void
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
     * @param Player $pl
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
     * @param CommandSender $sender
     * @param string $key
     * @param array<string, string> $replaces
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
        $this->translationMessages = [];

        foreach ($resources as $resource) {
            $normalizedPath = Path::normalize($resource->getPathname());
            if (!preg_match("/languages\/[a-z]{2}.yml$/", $normalizedPath)) {
                continue;
            }

            preg_match("/^[a-z][a-z]/", $resource->getFilename(), $fileNameRes);

            if (!isset($fileNameRes[0])) {
                continue;
            }

            $langId = $fileNameRes[0];

            $this->translationMessages[$langId] = new Config(
                $this->getResourcePath(Path::join("languages", $langId . ".yml"))
            );
        }
    }

    /**
     * @return Config[]
     */
    public function getTranslationMessages(): array
    {
        return $this->translationMessages;
    }

    /**
     * @return Config
     */
    public function getDefaultMessages(): Config
    {
        return $this->defaultMessages;
    }

    /**
     * @return EconomyProvider|null
     */
    public function getEconomyProvider(): ?EconomyProvider
    {
        return $this->economyProvider;
    }
}
