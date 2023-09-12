<?php

/**
 * HotBlock | translated messages utils
 */

namespace surva\hotblock\utils;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use surva\hotblock\HotBlock;

class Messages
{
    private HotBlock $hotBlock;

    private ?CommandSender $sender;

    public function __construct(HotBlock $hotBlock, ?CommandSender $sender = null)
    {
        $this->hotBlock = $hotBlock;
        $this->sender = $sender;
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
        $prefLangId = null;

        if ($this->sender instanceof Player && $this->hotBlock->getConfig()->get("autodetectlanguage", true)) {
            preg_match("/^[a-z][a-z]/", $this->sender->getLocale(), $localeRes);

            if (isset($localeRes[0])) {
                $prefLangId = $localeRes[0];
            }
        }

        $defaultLangId = $this->hotBlock->getConfig()->get("language", "en");

        $tm = $this->hotBlock->getTranslationMessages();
        if ($prefLangId !== null && isset($tm[$prefLangId])) {
            $langConfig = $tm[$prefLangId];
        } else {
            $langConfig = $tm[$defaultLangId];
        }

        $rawMessage = $langConfig->getNested($key);

        if ($rawMessage === null || $rawMessage === "") {
            $rawMessage = $this->hotBlock->getDefaultMessages()->getNested($key);
        }

        if ($rawMessage === null) {
            return $key;
        }

        foreach ($replaces as $replace => $value) {
            $rawMessage = str_replace("{" . $replace . "}", $value, $rawMessage);
        }

        return $rawMessage;
    }
}
