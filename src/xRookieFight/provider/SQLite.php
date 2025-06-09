<?php

namespace xRookieFight\provider;

use SQLite3;
use xRookieFight\Main;

class SQLite {

    private SQLite3 $db;

    public function __construct() {
        $this->db = new SQLite3(Main::getInstance()->getDataFolder() . "database.db");
        $this->db->exec("CREATE TABLE IF NOT EXISTS miners (
            miner_id TEXT PRIMARY KEY,
            owner TEXT,
            items TEXT
        )");
    }

    public function addMiner(string $minerId, string $owner, string $items): void
    {
        $data = $this->db->prepare("INSERT INTO miners(miner_id, owner, items) VALUES (:miner_id, :owner, :items)");
        $data->bindParam(":miner_id", $minerId);
        $data->bindParam(":owner", $owner);
        $data->bindParam(":items", $items);
        $data->execute();
    }

    public function getMinerData(): array
    {
        $data = $this->db->prepare("SELECT * FROM miners");
        $control = $data->execute();
        $array = [];

        while ($rows = $control->fetchArray(SQLITE3_ASSOC)) {
            $array[] = $rows;
        }
        return $array;
    }

    public function getMiner(string $minerId): ?string
    {
        foreach ($this->getMinerData() as $datum){
            if ($datum["miner_id"] == $minerId){
                return $datum["items"];
            }
        }
        return null;
    }


    public function updateMiner(string $minerId, string $items): void
    {
        $data = $this->db->prepare("UPDATE miners SET items = :items WHERE miner_id = :miner_id");
        $data->bindParam(":miner_id", $minerId);
        $data->bindParam(":items", $items);
        $data->execute();
    }

    public function removeMiner(string $minerId): void
    {
        $data = $this->db->prepare("DELETE FROM miners WHERE miner_id = :miner_id");
        $data->bindParam(":miner_id", $minerId);
        $data->execute();
    }

    public function close(): void {
        $this->db->close();
    }
}