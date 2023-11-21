<?php

namespace Plugin\dh_bonuspunkte\source\classes\points;

use DateTime;
use JTL\Customer\Customer;
use Plugin\dh_bonuspunkte\source\classes\history\LastRewarded;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;
use Plugin\dh_bonuspunkte\source\interfaces\rewards\IReward;

abstract class AbstractPoints implements IReward
{
    /** @var array The array of arguments */
    private array $_arguments;

    /**
     * Constructs a reward, initialized with an array of arguments
     * @param array $arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->_arguments = $arguments;
        return $this;
    }

    /**
     * Get the current customer that is targeted to obtain the reward
     * @return Customer
     */
    protected abstract function getCurrentCustomer(): Customer;

    /**
     * This abstract function is needed for executing the logic
     * for a single reward entry.
     * @return void
     */
    public abstract function executeRewardLogic(): void;

    /**
     * Get all arguments of the argument array, if none is set, it
     * will return an empty array.
     * @return array All arguments without validating
     */
    protected function getArguments(): array {
        return $this->_arguments ?? [];
    }

    /**
     * Get a single argument of the argument array, identified by the array key.
     * @param string $argumentKey The array key
     * @return mixed Anything that is in the corresponding array value
     */
    protected function getArgumentByKey(string $argumentKey): mixed
    {
        return $this->_arguments[$argumentKey] ?? null;
    }

    /**
     * Get the object with the saved times of last rewards. In some cases
     * it is only allowed to gain points in a defined interval, so the last
     * reward time can be accessed and saved over this object.
     * @return LastRewarded
     */
    protected function getLastRewarded(): LastRewarded
    {
        return new LastRewarded($this->getCurrentCustomer());
    }

    /**
     * Get the seconds of the given time interval string and after a match
     * get the corresponding constant from the LastRewarded class
     */
    protected function getTimeIntervalToSeconds(string $timeIntervalString): int
    {
        return match ($timeIntervalString) {
            "YEAR" => LastRewarded::SECONDS_YEAR,
            "MONTH" => LastRewarded::SECONDS_MONTH,
            "WEEK" => LastRewarded::SECONDS_WEEK,
            default => LastRewarded::SECONDS_DAY,
        };
    }

    /**
     * Get the current date in a formatted string in
     * day.month.year (e.g. 13.12.2023)
     * @return string
     */
    protected function getDateFormatted(): string {
        return (new DateTime())->format("d.m.Y");
    }

    /**
     * Creates a reward entry by the given parameters
     * @param int $bonusPoints The amount of bonus points
     * @param string|null $customText The text for the entry
     * @param int|null $orderId The order id if the entry is related to an order
     * @param bool $isValued If the points are valued, otherwise it needs manual unlocking
     * @return void
     */
    protected function createRewardEntry(int $bonusPoints, ?string $customText = null, ?int $orderId = null, bool $isValued = true): void
    {
        $userHistoryEntry = new UserHistoryEntry();
        if(is_null($customText)) {
            $customText = sprintf("Gutschrift am: %s", $this->getDateFormatted());
        }
        $customerId = $this->getCurrentCustomer()->kKunde;
        $newEntry = $userHistoryEntry->createEntry($bonusPoints, $customText, $customerId, $isValued, $orderId);
        $newEntry->save();
    }
}