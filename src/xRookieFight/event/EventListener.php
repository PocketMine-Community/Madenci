<?php

namespace xRookieFight\event;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerEntityInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\player\Player;
use xRookieFight\entity\MinerEntity;
use xRookieFight\forms\MinerMainForm;
use xRookieFight\inventory\MinerStorageInventory;
use xRookieFight\Manager;

class EventListener implements Listener
{

    function onItemUse(PlayerItemUseEvent $event) : void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->hasNamedTag() && $item->getNamedTag()->getTag("miner")){
            $event->cancel();
            $player->getInventory()->remove($item);
            Manager::spawnMiner($player, $item->getNamedTag()->getInt("level"));
        }
    }

    function onDamage(EntityDamageEvent $event) : void
    {
        if ($event->getEntity() instanceof MinerEntity){
            if ($event instanceof EntityDamageByEntityEvent){
                if ($event->getDamager() instanceof Player) {
                    if ($event->getEntity()->saveNBT()->getTag("owner") && ($event->getEntity()->saveNBT()->getString("owner") == $event->getDamager()->getName())) {
                        $event->getDamager()->sendForm(new MinerMainForm($event->getEntity()));
                    }
                }
            }
            $event->cancel();
        }
    }

    function onEntityInteract(PlayerEntityInteractEvent $event) : void
    {
        if ($event->getEntity() instanceof MinerEntity){
            if ($event->getEntity()->saveNBT()->getTag("owner") && ($event->getEntity()->saveNBT()->getString("owner") == $event->getPlayer()->getName())) {
                if ($event->getPlayer()->isSneaking()){
                    MinerStorageInventory::sendMenu($event->getPlayer(), $event->getEntity());
                } else {
                    $event->getPlayer()->sendForm(new MinerMainForm($event->getEntity()));
                }
            }
            $event->cancel();
        }
    }
}