<?php

namespace xRookieFight\task;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\World;
use xRookieFight\entity\MinerEntity;
use xRookieFight\Main;
use xRookieFight\Manager;

class MinerTask extends Task
{

    public function __construct(private MinerEntity $miner){}


    function onRun() : void {
        $miner = $this->miner;

        try {
            $position = $miner->getPosition();
            $world = $miner->getWorld();
        } catch (\Throwable $e) {
            return;
        }

        $level = $miner->getLevel();
        $radius = min(7, 3 + ($level * 2));

        $breakSpeed = max(5, 20 - ($level * 5));

        $nearestStone = null;
        $nearestDistance = $radius + 1;
        for($x = -$radius; $x <= $radius; $x++) {
            for($y = -$radius; $y <= $radius; $y++) {
                for($z = -$radius; $z <= $radius; $z++) {
                    $checkPos = $position->add($x, $y, $z);
                    $block = $world->getBlock($checkPos);

                    if($block->getTypeId() === BlockTypeIds::STONE || $block->getTypeId() === BlockTypeIds::COBBLESTONE) {
                        $distance = $position->distance($checkPos);
                        if($distance <= $radius && $distance < $nearestDistance) {
                            $nearestStone = $checkPos;
                            $nearestDistance = $distance;
                        }
                    }
                }
            }
        }

        if($nearestStone !== null) {
            Main::getInstance()->getScheduler()->scheduleDelayedTask(new class($miner, $nearestStone, $world) extends Task {

                public array $slotTypes = [
                    1 => 5,
                    2 => 27,
                    3 => 54
                ];
                public function __construct(
                    private MinerEntity $miner,
                    private Vector3 $stonePos,
                    private World $world
                ) {}

                public function onRun(): void
                {
                    if ($this->miner->isClosed()) return;

                    $this->miner->lookAt($this->stonePos);
                    $this->miner->broadcastAnimation(new ArmSwingAnimation($this->miner));
                    $this->world->addParticle($this->stonePos, new BlockBreakParticle($this->world->getBlock($this->stonePos)));
                    $cobblestone = $this->world->getBlock($this->stonePos)->getTypeId() === BlockTypeIds::COBBLESTONE;
                    $this->world->setBlock($this->stonePos, VanillaBlocks::AIR());

                    $data = Main::getProvider()->getMiner($this->miner->getMinerId());
                    if (is_null($data)) {
                        $this->getHandler()->cancel();
                        return;
                    }
                    $arrayUns = Manager::deserializeItems($data);

                    $tas = $cobblestone ? VanillaBlocks::COBBLESTONE()->asItem() : VanillaBlocks::STONE()->asItem();

                    $eklenebildi = false;

                    for ($i = 0; $i <= $this->slotTypes[$this->miner->getLevel()] - 1; $i++) {
                        if (isset($arrayUns[$i])) {
                            if ($arrayUns[$i]->getTypeId() === $tas->getTypeId() && $arrayUns[$i]->getCount() < 64) {
                                $arrayUns[$i]->setCount($arrayUns[$i]->getCount() + 1);
                                $eklenebildi = true;
                                break;
                            }
                        } else {
                            $tas->setCount(1);
                            $arrayUns[$i] = $tas;
                            $eklenebildi = true;
                            break;
                        }
                    }

                    if ($eklenebildi) {
                        $arraySd = Manager::serializeItems($arrayUns);
                        Main::getProvider()->updateMiner($this->miner->getMinerId(), $arraySd);
                    } else {
                        $this->miner->getWorld()->dropItem($this->miner->getPosition()->add(1,0,1), VanillaBlocks::STONE()->asItem());
                    }
                }
            }, $breakSpeed);
        }
    }
}