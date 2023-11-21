<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use DateTime;
use Exception;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;

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
    private ?DateTime $createdAt;
    // The date when the points were valued (e.g. when the order was completed)
    private ?DateTime $valuedAt;

    /**
     * It is valued if the dates are set, and it's valued after it was created
     */
    public function isValued(): bool {
        if($this->valuedAt == null || $this->createdAt == null) {
            return false;
        }
        // Negative amounts are instantly valued, e.g. for trade-in or points to shop balance exchange
        if($this->getPoints() < 0) return true;
        // Otherwise check if the minimum days for unlock are fulfilled
        return $this->isMinimumUnlockInDaysFulfilled();
    }

    /**
     * Get the points that were accounted
     */
    public function getPoints(): int {
        return $this->points;
    }

    /**
     * Get the text for this history entry
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return DateTime|null
     * @noinspection PhpUnused Is used in the template
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime|null
     * @noinspection PhpUnused Is used in the template
     */
    public function getValuedAt(): ?DateTime
    {
        return $this->valuedAt;
    }

    /**
     * Get the id of this entry
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Load the entry from the database array and return the object
     * @noinspection PhpUnhandledExceptionInspection
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
     * Get the order id if the entry is related to an order
     * @return int|null
     * @noinspection PhpUnused
     */
    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    /**
     * Set the valuedAt date for the given order id
     */
    public static function setValuedAtForOrderNow(int $kBestellung, bool $isValued = true): void
    {
        if($kBestellung == 0) return;
        $database = Shop::Container()->getDB();
        $data = $database->queryPrepared("SELECT * FROM " . self::TABLE_NAME . " WHERE orderId = :orderId", [":orderId" => $kBestellung], ReturnType::SINGLE_ASSOC_ARRAY);
        if($data == null || count($data) == 0) return;
        $entry = new UserHistoryEntry();
        $entry->fromDatabase($data);
        if($isValued) {
            $entry->valuedAt = new DateTime();
        } else {
            $entry->valuedAt = new DateTime("0000-00-00 00:00:00");
        }
        $entry->save();
    }

    /**
     * Create a new entry from the given parameters
     */
    public function createEntry(int $points, string $text, int $userId, bool $isValued = false, ?int $orderId = null): UserHistoryEntry {
        if($points <= 0) {
            DebugManager::addMessage(new DebugMessage("Punkte können nicht gutgeschrieben werden, da der Betrag <= 0 ist.", [
                "object" => $this,
            ]));
        }

        $dateNow = new DateTime();
        $this->id = null;
        $this->createdAt = $dateNow;
        if($isValued) {
            $this->valuedAt = $dateNow;
        } else {
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
    public function save(): self {
        try {
            if($this->getPointsWithCap()) {
                $database = Shop::Container()->getDB();
                $insertObject = new \stdClass();
                if(isset($this->id)) {
                    $insertObject->id = $this->id;
                }
                $insertObject->userId = $this->userId;
                $insertObject->orderId = $this->orderId;
                $insertObject->text = $this->text;
                $insertObject->points = $this->getPointsWithCap();
                $insertObject->createdAt = $this->createdAt?->format("Y-m-d H:i:s");
                $insertObject->valuedAt = $this->valuedAt?->format("Y-m-d H:i:s");
                $this->id = $database->upsert(self::TABLE_NAME, $insertObject);
                DebugManager::addMessage(new DebugMessage("Neuer Punkte-Eintrag wurde gespeichert.", [
                    "id" => $this->id
                ]));
            }

        } catch(Exception) {
            DebugManager::addMessage(new DebugMessage("Punkte-Eintrag konnte nicht gespeichert werden, unbekannter Fehler.", []));
        }
        return $this;
    }

    /**
     * Check if the requirement for unlocking the points after a certain amount of days
     * @return bool
     */
    public function isMinimumUnlockInDaysFulfilled(): bool
    {
        $unlockInDays = PluginSettingsAccessor::getRewardUnlockAfterDays();
        if ($unlockInDays > 0) {
            $currentDateTime = new DateTime();
            try {
                $createdAtClone = (new DateTime($this->getCreatedAt()->format(DATE_ATOM)));
                $createdAtClone->modify(sprintf("+%d day", $unlockInDays));
                return $createdAtClone < $currentDateTime;
            } catch (Exception) {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Get the points with the cap if the cap is enabled. The cap can be enabled
     * and its value can be set in the plugin settings.
     * @return int
     */
    public function getPointsWithCap(): int
    {
        // Check if the cap is enabled
        if (PluginSettingsAccessor::getIsUserPointsCappedEnabled()) {
            $userHistory = new UserHistory((new Customer())->loadFromDB($this->userId));
            $currentPoints = $userHistory->getTotalValuedPoints(); // 31
            $cappedPoints = PluginSettingsAccessor::getUserPointsCappedAt(); // 30
            $estimatedPoints = $currentPoints + $this->points;
            // Check if the estimated points are below the cap
            if ($estimatedPoints > $cappedPoints) {
                // Otherwise add the difference between the cap and the current points
                $this->points = $currentPoints;
            }
        }
        return $this->points;
    }
}