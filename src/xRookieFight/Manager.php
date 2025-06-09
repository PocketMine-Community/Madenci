<?php

namespace xRookieFight;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use xRookieFight\entity\MinerEntity;

class Manager
{

    static function getMinerEgg(int $level = 1) : Item
    {
        $item = VanillaItems::EGG();

        $item->setCustomName("§dMadenci Yumurtası\n§6Seviye $level");
        $item->setLore(["Sv. $level"]);
        $item->getNamedTag()->setInt("level", $level);
        $item->getNamedTag()->setInt("miner", time());
        return $item;
    }

    static function spawnMiner(Player $owner, int $level) : void
    {
        $nbt = new CompoundTag();
        $nbt->setString("owner", $owner->getName());
        $nbt->setInt("level", $level);
        $nbt->setString("minerId", uniqid());

        $entity = new MinerEntity($owner->getLocation(), $owner->getSkin(), $nbt);

        $entity->setNameTag("§6Madenci Minyon §7(Sv. {$nbt->getInt("level")})\n§aSahibi: §2" . $owner->getName());
        $entity->setNameTagAlwaysVisible();
        $entity->getInventory()->setItemInHand(VanillaItems::DIAMOND_PICKAXE());

        $entity->setRotation($owner->getLocation()->getYaw(),0);

        $entity->spawnToAll();
        Main::getProvider()->addMiner($entity->getMinerId(), $owner->getName(), self::serializeItems([0 => VanillaBlocks::STONE()->asItem()]));
    }

    public static function deserializeItems(string $data): array
    {
        $contents = [];
        $inventoryTag = Main::$serializer->read(zlib_decode(base64_decode($data)))->mustGetCompoundTag()->getListTag("Inventory");
        /** @var CompoundTag $tag */
        foreach ($inventoryTag as $tag) {
            $contents[$tag->getByte("Slot")] = Item::nbtDeserialize($tag);
        }
        return $contents;
    }

    public static function serializeItems(array $contentsR): string
    {
        $contents = [];
        foreach ($contentsR as $slot => $item) {
            $contents[] = $item->nbtSerialize($slot);
        }
        return base64_encode(zlib_encode(Main::$serializer->write(new TreeRoot(CompoundTag::create()
            ->setTag("Inventory", new ListTag($contents, NBT::TAG_Compound))
        )), ZLIB_ENCODING_GZIP));
    }
}