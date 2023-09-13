<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;

use DateTime;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;

class LastRewarded
{
    // The seconds of a year
    public const SECONDS_YEAR = 31104000;
    // The seconds of a month
    public const SECONDS_MONTH = 2592000;
    // The seconds of a week
    public const SECONDS_WEEK = 604800;
    // The seconds of a day
    public const SECONDS_DAY = 86400;
    // The table name in the database for storing last rewarded dates
    private const TABLE_NAME = "dh_bonus_last_rewarded";

    // The ID of the entry in the database
    private int $id;
    // The ID of the customer
    private int $customerId;
    // The time of the last visit
    private ?DateTime $visitAt = null;
    // The time of the last login
    private ?DateTime $loginAt = null;

    /**
     * LastRewarded constructor.
     * @param Customer $customer The customer object to load the data from
     */
    public function __construct(Customer $customer)
    {
        $this->loadCustomer($customer);
    }

    /**
     * Load the object from the database for the given customer
     * If the customer does not exist, create a new entry
     */
    private function loadCustomer(Customer $customer): self
    {
        if ($customer->kKunde == null || $customer->kKunde <= 0) return $this;
        $database = Shop::Container()->getDB();
        $databaseResult = $database->queryPrepared("SELECT * FROM ".self::TABLE_NAME." WHERE userId = :kKunde", ["kKunde" => $customer->kKunde], ReturnType::ARRAY_OF_ASSOC_ARRAYS);
        if ($databaseResult) {
            $this->loadFromDatabase($databaseResult);
        } else {
            $this->createEntryForCustomer($customer);
        }

        return $this;
    }

    /**
     * Load the object attributes from the database result
     */
    private function loadFromDatabase(array $databaseResult) {
        $firstData = $databaseResult[0];
        $this->id = $firstData["id"];
        $this->customerId = $firstData["userId"];
        $this->visitAt = ($firstData["visitAt"] != null) ? new DateTime($firstData["visitAt"]) : null;
        $this->loginAt = ($firstData["loginAt"] != null) ? new DateTime($firstData["loginAt"]) : null;
    }

    /**
     * Create the entry in the database for the given customer if it does not exist
     * and fills this object with the data
     */
    private function createEntryForCustomer(Customer $customer)
    {
        $database = Shop::Container()->getDB();
        // Update the object
        $this->customerId = $customer->kKunde;
        $this->loginAt = null;
        $this->visitAt = null;
        // Create the entry in the database
        $insertObj = new \stdClass();
        $insertObj->userId = $this->customerId;
        $insertObj->loginAt = $this->loginAt;
        $insertObj->visitAt = $this->visitAt;
        // Set the ID after the insert
        $this->id = $database->insert(self::TABLE_NAME, $insertObj);
    }

    /**
     * Get the last visit date
     * @return DateTime|null
     */
    public function getVisitAt(): ?DateTime
    {
        return $this->visitAt ?? new DateTime("0000-00-00 00:00:00");
    }

    /**
     * Get the last login date
     * @return DateTime|null
     */
    public function getLoginAt(): ?DateTime
    {
        return $this->loginAt ?? new DateTime("0000-00-00 00:00:00");
    }

    /**
     * Check if the given seconds are past since the given date
     */
    public function isSecondsSinceDatePast(int $seconds, DateTime $date): bool
    {
        $now = new DateTime();
        $diff = $now->getTimestamp() - $date->getTimestamp();
        return $diff >= $seconds;
    }

    /**
     * Set the last visit date to the current date
     */
    public function setVisitAt(): self
    {
        $this->visitAt = new DateTime();
        return $this;
    }

    /**
     * Set the last login date to the current date
     */
    public function setLoginAt(): self
    {
        $this->loginAt = new DateTime();
        return $this;
    }

    /**
     * Save the updated DateTimes to the database
     */
    public function save(): void
    {
        $updateObj = new \stdClass();
        if ($this->loginAt != null) {
            $updateObj->loginAt = $this->loginAt->format("Y-m-d H:i:s");
        }
        if ($this->visitAt != null) {
            $updateObj->visitAt = $this->visitAt->format("Y-m-d H:i:s");
        }
        Shop::Container()->getDB()->update(self::TABLE_NAME, "id", $this->id, $updateObj);
    }
}