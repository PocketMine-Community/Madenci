<?php

namespace xRookieFight\inventory;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\player\Player;
use xRookieFight\entity\MinerEntity;
use xRookieFight\Main;
use xRookieFight\Manager;

class MinerStorageInventory
{
    static function sendMenu(Player $player, MinerEntity $miner) : void
    {
        $minerId = $miner->getMinerId();
        $level = $miner->getLevel();

        $menuType = match($level) {
            2 => InvMenuTypeIds::TYPE_CHEST,
            3 => InvMenuTypeIds::TYPE_DOUBLE_CHEST,
            default => InvMenuTypeIds::TYPE_HOPPER
        };

        $menu = InvMenu::create($menuType);

        $inventory = Manager::deserializeItems(Main::getProvider()->getMiner($minerId));
        foreach ($inventory as $slot => $item) {
            if($slot < $menu->getInventory()->getSize()) {
                $menu->getInventory()->setItem($slot, $item);
            }
        }


        $menu->setInventoryCloseListener(function () use ($miner, $menu, $minerId): void {
            $items = Manager::serializeItems($menu->getInventory()->getContents());
            Main::getProvider()->updateMiner($minerId, $items);
        });

        $menu->send($player, "Madenci Deposu (Sv. " . $level . ")");
    }
}