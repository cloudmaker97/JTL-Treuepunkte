<?php
namespace Plugin\dh_bonuspunkte\source\classes\history;
use JTL\Customer\Customer;
use JTL\DB\ReturnType;
use JTL\Shop;
class UserHistory {
    /** @var UserHistoryEntry[] $historyEntries */
    private array $historyEntries = [];

    /**
     * Initialize the point history for a certain user
     * @param Customer $customer
     */
    public function __construct(Customer $customer) {
        $this->loadFromCustomer($customer);
    }

    /**
     * Load all the history entries for a certain user
     * @param Customer $customer
     * @return void
     */
    private function loadFromCustomer(Customer $customer): void
    {
        $this->historyEntries = [];
        $results = Shop::Container()->getDB()->queryPrepared("SELECT * FROM ".UserHistoryEntry::TABLE_NAME." WHERE userId = :kKunde ORDER BY createdAt DESC", ["kKunde" => $customer->getID()], ReturnType::ARRAY_OF_ASSOC_ARRAYS);
        foreach($results as $result) {
            $historyEntry = new UserHistoryEntry();
            $this->historyEntries[] = $historyEntry->fromDatabase($result);
        }
    }

    /**
     * The total amount of valued points that the user has
     * @return int Result of the whole history
     * @noinspection PhpUnused Is used in the template
     */
    public function getTotalValuedPoints(): int
    {
        $totalSum = 0;
        foreach($this->historyEntries as $entry) {
            if($entry->isValued()) {
                $totalSum += $entry->getPoints();
            }
        }
        return $totalSum;
    }

    /**
     * The total amount of not valued points yet
     * @noinspection PhpUnused Is used in the template
     * @return int
     */
    public function getNotValuedPoints(): int {
        $totalSum = 0;
        foreach($this->historyEntries as $entry) {
            if(!$entry->isValued()) {
                $totalSum += $entry->getPoints();
            }
        }
        return $totalSum;
    }

    /**
     * Get the history entries
     * @noinspection PhpUnused Is used in the template
     * @return UserHistoryEntry[]
     */
    public function getEntries(): array
    {
        return $this->historyEntries;
    }
}