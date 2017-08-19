<?php
/**
 * Created by PhpStorm.
 * User: Jarne
 * Date: 10.08.16
 * Time: 19:02
 */

namespace surva\hotblock;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;

class EventListener implements Listener {
    /* @var HotBlock */
    private $hotBlock;

    public function __construct(HotBlock $hotBlock) {
        $this->hotBlock = $hotBlock;
    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        $world = $player->getLevel();
        $block = $world->getBlock($player->floor()->subtract(0, 1));

        if($world->getName() == $this->getHotBlock()->getConfig()->get("world")) {
            switch($block->getId()) {
                case Block::PLANKS:
                    $player->sendTip("§aYou're safe!");
                    break;
                case Block::END_STONE:
                    $player->sendTip("§eGo!");
                    break;
                case Block::NETHERRACK:
                    $player->sendTip("§cYou're poisoned!");

                    $effect = Effect::getEffect(Effect::POISON);
                    $effect->setVisible(true);
                    $effect->setDuration(50);

                    $player->addEffect($effect);
                    break;
                case Block::QUARTZ_BLOCK:
                    if(count($world->getPlayers()) < $this->getHotBlock()->getConfig()->get("players")) {
                        $player->sendTip("§cThere must be " . $this->getHotBlock()->getConfig()->get("players") . " players online");
                    } else {
                        $player->sendTip("§eYou're standing on the §l§cHot§6Block§r§e! §bMove!");
                        $player->sendPopup("§eYou have §a" . $this->getHotBlock()->getEconomy()->myMoney($player) . " §bCoins");

                        $this->getHotBlock()->getEconomy()->addMoney($player, 1, false, "HotBlock");
                    }
                    break;
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));

        if($world->getName() == $this->getHotBlock()->getConfig()->get("world")) {
            switch($block->getId()) {
                case Block::PLANKS:
                    $event->setCancelled(true);
                    break;
            }
        }
    }

    /**
     * @return HotBlock
     */
    public function getHotBlock(): HotBlock {
        return $this->hotBlock;
    }
}
