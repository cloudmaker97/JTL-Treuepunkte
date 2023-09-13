<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use DateTime;
use JTL\Shop;

class UserHistoryEntry {
    // The name of the table in the database
    public const TABLE_NAME = "dh_bonus_history";

    // The id of the entry in the database
    private ?int $id;
    // The id of the user in the database
    private int $userId;
    // The id of the order in the database
    private ?int $orderId;
    // The message that is displayed to the user
    private string $text;
    // The amount of points that were added to the account
    private int $points;
    // The date when the points were added to the account
    private DateTime $createdAt;
    // The date when the points were valued (e.g. when the order was completed)
    private ?DateTime $valuedAt;

    /**
     * It is valued if the dates are set and it's valued after it was created
     */
    private function isValued(): bool {
        if($this->valuedAt == null || $this->createdAt == null) {
            return false;
        }
        return $this->createdAt >= $this->valuedAt;
    }

    /**
     * Get the points that were accounted
     */
    public function getPoints(): int {
        return $this->points;
    }

    public function loadId(int $id): UserHistoryEntry {
        $this->id = $id;
        $database = Shop::Container()->getDB();
        $result = $database->queryPrepared("SELECT * FROM " . self::TABLE_NAME . " WHERE id = :id", ["id" => $id]);
        var_dump($result);
        return $this;
    }

    /**
     * Create a new entry from the given parameters
     */
    public function createEntry(int $points, string $text, int $userId, bool $isValued = false, ?int $orderId = null): UserHistoryEntry {
        $dateNow = new DateTime();
        $this->id = null;
        if($isValued) {
            $this->createdAt = $dateNow;
            $this->valuedAt = $dateNow;
        } else {
            $this->createdAt = $dateNow;
            $this->valuedAt = null;
        }
        $this->orderId = $orderId;
        $this->points = $points;
        $this->userId = $userId;
        $this->text = $text;
        return $this;
    }

    public function save() {
        $database = Shop::Container()->getDB();
        $insertObject = new \stdClass();
        $insertObject->userId = $this->userId;
        $insertObject->orderId = $this->orderId;
        $insertObject->text = $this->text;
        $insertObject->points = $this->points;
        $insertObject->createdAt = $this->createdAt->format("Y-m-d H:i:s");
        $insertObject->valuedAt = $this->valuedAt == null ? null : $this->valuedAt->format("Y-m-d H:i:s");
        $database->insert(self::TABLE_NAME, $insertObject);
    }

}