<?php

/* 
 *  Plugin developed by SURVA.ml Dev Team
 *  Homepage: www.surva.ml - Mail: support@surva.ml
 */

namespace jjmc\HotBlock;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\entity\Effect;

class HotBlock extends PluginBase implements Listener {
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->world = $this->getConfig()->get("world");
        $this->players = $this->getConfig()->get("players");
        $this->getServer()->loadLevel($this->world);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    }
    
    public function onPlayerMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getLevel();
        $block = $world->getBlock($player->floor()->subtract(0, 1));
        
        if($world->getName() == $this->world) {
            switch($block->getId()) {
                case "5":
                    $player->sendTip("§aYou're safe!");
                    break;
                case "121":
                    $player->sendTip("§eGo!");
                    break;
                case "87":
                    $player->sendTip("§cYou'll poisoned!");
                    $effect = Effect::getEffect(Effect::POISON);
                    $effect->setVisible(true);
                    $effect->setDuration(50);
                    $player->addEffect($effect);
                    break;
                case "155":
                    if(count($world->getPlayers()) < $this->players) {
                        $player->sendTip("§cThere must be {$this->players} online");
                    } else {
                        $player->sendTip("§eYou're standing on the §l§cHot§6Block§r§e! §bMove!");
                        $this->economy->addMoney($player, 1, false, "HotBlock");
                        $player->sendPopup("§eYou have §a".$this->economy->myMoney($player)." §bCoins§e.");
                    }
                    break;
            }
        }
    }
    
    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));
        
        if($world->getName() == $this->world) {
            switch($block->getId()) {
                case "5":
                    $event->setCancelled(true);
                    break;
            }
        }
    }
}
