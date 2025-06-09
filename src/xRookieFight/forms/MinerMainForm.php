<?php

namespace xRookieFight\forms;

use pocketmine\form\Form;
use pocketmine\player\Player;
use xRookieFight\entity\MinerEntity;
use xRookieFight\Main;
use xRookieFight\Manager;

class MinerMainForm implements Form
{

    function __construct(public MinerEntity $miner){}

    function jsonSerialize() : mixed
    {
        $level = $this->miner->getLevel();
        return [
            "type" => "form",
            "title" => "Madenci (Sv. $level)",
            "content" => "Bu menüden madenci ile alakalı işlemler yapabilirsin.",
            "buttons" => [
                [
                    "text" => $level >= 3
                    ? "Seviye Yükselt\n§l§cYÜKSELTİLEMEZ"
                    : "Seviye Yükselt",
                    "image" => [
                        "type" => "url",
                        "data" => "https://cdn-icons-png.flaticon.com/512/8041/8041359.png"
                    ]
                ],
                ["text" => "Görünüş Değiştir", "image" => ["type" => "url", "data" => "https://i.hizliresim.com/oppu8oq.png"]],
                ["text" => "§cMadenciyi Kaldır", "image" => ["type" => "url", "data" => "https://cdn-icons-png.flaticon.com/512/1828/1828843.png"]]
            ]
        ];
    }

    function handleResponse(Player $player, $data): void
    {
        if (is_null($data)) return;
        switch ($data) {
            case 0:
                if ($this->miner->getLevel() >= 3){
                    $player->sendMessage("§cMadencin zaten son seviye ve yükseltilemez.");
                    return;
                }

                $player->sendForm(new MinerUpgradeForm($this->miner));
                break;
			case 1:
				$this->miner->changeSkin($player->getSkin());
				$player->sendMessage("§aMadencinin görünüşü başarıyla değiştirildi.");
				break;
            case 2:
                $item = Manager::getMinerEgg($this->miner->getLevel());
                $this->miner->getWorld()->dropItem($this->miner->getPosition(), $item);
                Main::getProvider()->removeMiner($this->miner->getMinerId());

                $this->miner->flagForDespawn();

                $player->sendMessage("§aBaşarılı bir şekilde madenci kaldırıldı.");
                break;
        }
    }
}