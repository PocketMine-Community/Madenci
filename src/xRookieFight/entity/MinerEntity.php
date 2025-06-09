<?php

namespace xRookieFight\entity;

use Closure;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use xRookieFight\Main;
use xRookieFight\task\MinerTask;

class MinerEntity extends Human {

    private CompoundTag $nbt;
    private int $level;
    private string $owner;
    private string $minerId;
    private ?Closure $taskHandler = null;

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $this->nbt = $nbt;
        $this->level = $nbt->getInt("level");
        $this->owner = $nbt->getString("owner");
        $this->minerId = $nbt->getString("minerId");
        $this->setNameTag("§6Madenci Minyon §7(Sv. {$this->level})\n§aSahibi: §2" . $nbt->getString("owner"));
        $this->setNameTagAlwaysVisible();
        $this->setScale(0.6);
        $this->startMiningTask();
    }

    public function getName(): string
    {
        return "Madenci Minyon";
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();

        $nbt = $nbt->merge($this->nbt);

        $nbt->setString("owner", $this->owner);
        $nbt->setString("minerId", $this->minerId);
        $nbt->setInt("level", $this->level);
        return $nbt;
    }

    public function getLevel() : int
    {
        return $this->level;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getMinerId(): string
    {
        return $this->minerId;
    }

    public function setLevel(int $level): void
    {
        $this->nbt->setInt("level", $level);
        $this->level = $level;
        $this->setNameTag("§6Madenci Minyon §7(Sv. $this->level)\n§aSahibi: §2" . $this->getOwner());
    }

    public function startMiningTask(): void {
        if ($this->taskHandler !== null) {
            ($this->taskHandler)();
            $this->taskHandler = null;
        }

        $interval = max(10, 40 - ($this->getLevel() * 10));

        $task = new MinerTask($this);
        $handler = Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, $interval);

        $this->taskHandler = function() use ($handler) {
            $handler->cancel();
        };
    }

    public function flagForDespawn(): void
    {
        if ($this->taskHandler !== null) {
            ($this->taskHandler)();
        }
        parent::flagForDespawn();
    }

}