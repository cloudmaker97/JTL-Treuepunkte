<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;
class UserHistory {
    /** @var UserHistoryEntry[] $historyEntries */
    private $historyEntries = [];

    public function __construct(Customer $customer) {
        $this->loadFromCustomer($customer);
    }

    private function loadFromCustomer(Customer $customer) {
        $this->historyEntries = [];
        $results = Shop::Container()->getDB()->queryPrepared("SELECT * FROM dh_bonus_history WHERE userId = :kKunde ORDER BY createdAt DESC", ["kKunde" => $customer->getID()], ReturnType::ARRAY_OF_ASSOC_ARRAYS);
        var_dump($results);
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