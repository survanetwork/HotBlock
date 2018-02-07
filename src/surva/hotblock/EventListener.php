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
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $world = $player->getLevel();
        $block = $world->getBlock($player->floor()->subtract(0, 1));

        if($world->getName() === $this->getHotBlock()->getConfig()->get("world")) {
            switch($block->getId()) {
                case Block::PLANKS:
                    $player->sendTip($this->getHotBlock()->getMessage("ground.safe"));
                    break;
                case Block::END_STONE:
                    $player->sendTip($this->getHotBlock()->getMessage("ground.run"));
                    break;
                case Block::NETHERRACK:
                    $player->sendTip($this->getHotBlock()->getMessage("ground.poisoned"));

                    $effect = Effect::getEffect(Effect::POISON);
                    $effect->setVisible(true);
                    $effect->setDuration(50);

                    $player->addEffect($effect);
                    break;
                case Block::QUARTZ_BLOCK:
                    if(count($world->getPlayers()) < $this->getHotBlock()->getConfig()->get("players")) {
                        $player->sendTip($this->getHotBlock()->getMessage("block.lessplayers", array("count" => $this->getHotBlock()->getConfig()->get("players"))));
                    } else {
                        $player->sendTip($this->getHotBlock()->getMessage("block.move"));
                        $player->sendTip($this->getHotBlock()->getMessage("block.coins", array("count" => $this->getHotBlock()->getEconomy()->myMoney($player))));

                        $this->getHotBlock()->getEconomy()->addMoney($player, 1, false, "HotBlock");
                    }
                    break;
            }
        }
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event): void {
        $entity = $event->getEntity();
        $world = $entity->getLevel();
        $block = $world->getBlock($entity->floor()->subtract(0, 1));

        if($world->getName() === $this->getHotBlock()->getConfig()->get("world")) {
            if($block->getId() === Block::PLANKS) {
                $event->setCancelled();
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
