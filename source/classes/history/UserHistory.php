<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use JTL\Customer\Customer;
class UserHistory {
    /** @var UserHistoryEntry[] $historyEntries */
    private $historyEntries = [];

    public function __construct(Customer $customer) {
        // @todo Load from customer
        // @todo Adding and removing points
        // @todo Dashboard
    }

    /**
     * The total amount of points that the user has
     * @return int Result of the whole history
     */	
    public function getTotalPoints(): int
    {
        $totalSum = 0;
        foreach($this->historyEntries as $entry) {
            $totalSum += $entry->getPoints();
        }
        return $totalSum;
    }
}