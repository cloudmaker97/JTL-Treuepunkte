<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use DateTime;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;

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
    public function isValued(): bool {
        if($this->valuedAt == null || $this->createdAt == null) {
            return false;
        }
        return $this->createdAt <= $this->valuedAt;
    }

    /**
     * Get the points that were accounted
     */
    public function getPoints(): int {
        return $this->points;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getValuedAt(): ?DateTime
    {
        return $this->valuedAt;
    }

    /**
     * Get the points that were accounted
     */
    public function getId(): int {
        return $this->id;
    }


    /**
     * Load the entry from the database array and return the object
     */
    public function fromDatabase(array $data): self
    {
        $this->id = $data["id"];
        $this->userId = $data["userId"];
        $this->orderId = $data["orderId"];
        $this->text = $data["text"];
        $this->points = $data["points"];
        $this->createdAt = new DateTime($data["createdAt"]);
        $this->valuedAt = $data["valuedAt"] == null ? null : new DateTime($data["valuedAt"]);
        return $this;
    }

    /**
     * Create a new entry from the given parameters
     */
    public function createEntry(int $points, string $text, int $userId, bool $isValued = false, ?int $orderId = null): UserHistoryEntry {
        if($points == 0) {
            DebugManager::addMessage(new DebugMessage("Punkte können nicht gutgeschrieben werden, da der Betrag 0 ist.", [
                "object" => $this,
            ]));
            return $this;
        }

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

        DebugManager::addMessage(new DebugMessage("Neuer Punkte-Eintrag wurde initialisiert.", []));
        return $this;
    }

    /**
     * Save the entry to the database
     */
    public function save(): void {
        try {
            $database = Shop::Container()->getDB();
            $insertObject = new \stdClass();
            $insertObject->userId = $this->userId;
            $insertObject->orderId = $this->orderId;
            $insertObject->text = $this->text;
            $insertObject->points = $this->points;
            $insertObject->createdAt = $this->createdAt->format("Y-m-d H:i:s");
            $insertObject->valuedAt = $this->valuedAt == null ? null : $this->valuedAt->format("Y-m-d H:i:s");
            $this->id = $database->insert(self::TABLE_NAME, $insertObject);
            DebugManager::addMessage(new DebugMessage("Neuer Punkte-Eintrag wurde gespeichert.", [
                "id" => $this->id
            ]));
            var_dump($insertObject->valuedAt);

        } catch(\Exception $e) {
            DebugManager::addMessage(new DebugMessage("Punkte-Eintrag konnte nicht gespeichert werden, unbekannter Fehler.", []));
        }
    }
}