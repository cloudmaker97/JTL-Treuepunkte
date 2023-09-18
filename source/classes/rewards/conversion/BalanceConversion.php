<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards\conversion;

use JTL\Customer\Customer;

class BalanceConversion
{
    /** @var bool If the widget is enabled  */
    private bool $isEnabled;
    /** @var int The minimum amount of points for trade-in */
    private int $minimumTradeIn;
    /** @var int The amount of points that represent 1 euro */
    private int $pointsForOneEuro;
    /** @var int The amount of unlocked and valued points */
    private int $unlockedPoints;
    /** @var Customer The current customer */
    private Customer $customer;

    /**
     * Get the current customer
     * @return Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    /**
     * Set the current customer
     * @param Customer $customer
     * @return void
     */
    public function setCustomer(Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Check if the widget is enabled by plugin settings
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Set if the widget is enabled by plugin settings
     * @param bool $isEnabled
     * @return void
     */
    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * Get the minimum amount of points to trade-in for shop balance
     * @return int
     */
    public function getMinimumTradeIn(): int
    {
        return $this->minimumTradeIn;
    }

    /**
     * Set the minimum amount of points to trade-in for shop balance
     * @param int $minimumTradeIn
     * @return void
     */
    public function setMinimumTradeIn(int $minimumTradeIn): void
    {
        $this->minimumTradeIn = $minimumTradeIn;
    }

    /**
     * Get the points that are needed to exchange them to one euro
     * @return int
     */
    public function getPointsForOneEuro(): int
    {
        return $this->pointsForOneEuro;
    }

    /**
     * Set the points that are needed to exchange them to one euro
     * @param int $pointsForOneEuro
     * @return void
     */
    public function setPointsForOneEuro(int $pointsForOneEuro): void
    {
        $this->pointsForOneEuro = $pointsForOneEuro;
    }

    /**
     * Get the amount of all unlocked points (yet)
     * @return int
     */
    public function getUnlockedPoints(): int
    {
        return $this->unlockedPoints;
    }

    /**
     * Set the amount of all unlocked points (yet)
     * @param int $unlockedPoints
     * @return void
     */
    public function setUnlockedPoints(int $unlockedPoints): void
    {
        $this->unlockedPoints = $unlockedPoints;
    }

    /**
     * Check if the exchange/conversion widget is enabled for a user.
     * This is usually the case if the minimum trade-in is lower than the unlocked points
     * @return bool
     * @noinspection PhpUnused Used in template
     */
    public function isWidgetActiveForUser(): bool
    {
        return $this->getMinimumTradeIn() <= $this->getUnlockedPoints();
    }

    /**
     * Calculate an amount of points to a float, representing a real currency euro
     * @param int $points
     * @return float
     * @noinspection PhpUnused Used in template
     */
    public function calculatePointsToEuro(int $points): float {
        return $points / $this->getPointsForOneEuro();
    }

    /**
     * Get the shop balance from the current user
     * @param bool $localized If the balance should be localized
     * @return string
     * @noinspection PhpUnused Used in template
     */
    public function getShopBalance(bool $localized = false): string
    {
        if($localized) {
            return $this->getCustomer()->gibGuthabenLocalized();
        } else {
            return $this->getCustomer()->fGuthaben;
        }
    }

}