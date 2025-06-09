<?php

namespace xRookieFight;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use xRookieFight\entity\MinerEntity;
use xRookieFight\event\EventListener;
use xRookieFight\provider\SQLite;
use xRookieFight\task\MinerTask;

class Main extends PluginBase {

    private static self $instance;
    public static BigEndianNbtSerializer $serializer;
    private static SQLite $provider;

    public static function getInstance(): self {
        return self::$instance;
    }

    public static function getProvider(): SQLite {
        return self::$provider;
    }

    public function onEnable(): void {
        self::$instance = $this;
        self::$provider = new SQLite();
        self::$serializer = new BigEndianNbtSerializer();

        $this->getLogger()->info("Plugin aktif! Made by xRookieFight");

        if (!InvMenuHandler::isRegistered()) InvMenuHandler::register($this);

        EntityFactory::getInstance()->register(MinerEntity::class, static function(World $world, CompoundTag $nbt): MinerEntity{
            return new MinerEntity(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
        }, ["MinerEntity"]);

        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

    }

    public function onDisable(): void
    {
        if (self::$provider) self::$provider->close();
        $this->getLogger()->alert("Plugin de-aktif!");
    }
    

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() == "madenciver") {
            $sender->getInventory()->addItem(Manager::getMinerEgg());
            return true;
        }
        return false;
    }

}
