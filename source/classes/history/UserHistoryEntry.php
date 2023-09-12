<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use JTL\Services\JTL\Validation\Rules\DateTime;

class UserHistoryEntry {
    // The id of the entry in the database
    private int $id;
    // The message that is displayed to the user
    private int $text;
    // The amount of points that were added to the account
    private int $points;
    // The date when the points were added to the account
    private DateTime $createdAt;
    // The date when the points were valued (e.g. when the order was completed)
    private DateTime $valuedAt;

    /**
     * It is valued if the dates are set and it's valued after it was created
     */
    private function isValued(): bool {
        if($this->valuedAt == null || $this->createdAt == null) {
            return false;
        }
        return $this->createdAt > $this->valuedAt;
    }

    public function getPoints(): int {
        return $this->points;
    }
}