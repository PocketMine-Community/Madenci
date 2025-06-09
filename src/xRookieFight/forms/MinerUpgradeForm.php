<?php

namespace xRookieFight\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use xRookieFight\entity\MinerEntity;
use NovaMC\provider\Economy;

class MinerUpgradeForm implements Form
{

    public $upgradePrices = [
        1 => 50000,
        2 => 100000,
        3 => 200000
    ];

    public function __construct(public MinerEntity $miner){}

    function jsonSerialize() : mixed
    {

        $level = $this->miner->getLevel();
        return [
            "type" => "modal",
            "title" => "Madenci Yükseltme",
            "content" => "Madencinin seviyesini yükseltmek istiyor musun?\nBu durumda madencinin seviyesi §6Sv. ". $level + 1 . " §folacaktır.\nYükseltme ücreti: §6" . number_format($this->upgradePrices[$level]) . " TL\n§fBu işlemi onaylıyor musun?",
            "button1" => "§aOnayla",
            "button2" => "§cGeri"
        ];
    }

    function handleResponse(Player $player, $data): void
    {
        if ($data){
            $level = $this->miner->getLevel();
            $upgradedLevel = $level+1;

            if ($upgradedLevel > 3){
                $player->sendMessage("§cBu madenci zaten son seviyede ve yükseltilemez.");
                return;
            }

            if (Economy::getMoney($player->getName()) >= $this->upgradePrices[$level]){
                Economy::takeMoney($player->getName(), $this->upgradePrices[$level]);
                $this->miner->setLevel($upgradedLevel);
                $player->sendMessage("§aBaşarılı bir şekilde madencinin seviyesini §2Sv. $upgradedLevel §ayaptın.");
            } else {
                $player->sendMessage("§cYeterli paran yok.");
            }



        } else $player->sendForm(new MinerMainForm($this->miner));
    }
}