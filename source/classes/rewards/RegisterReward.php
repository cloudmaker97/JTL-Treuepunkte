<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards;

use Exception;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;

class RegisterReward extends AbstractReward
{
    /**
     * @throws Exception
     */
    protected function getCurrentCustomer(): Customer
    {
        $customerId = $this->getArgumentByKey("customerID");
        if (is_null($customerId)) {
            throw new Exception("Customer id is not set by event dispatcher");
        }
        return new Customer($customerId);
    }

    /**
     * @return void
     */
    public function executeRewardLogic(): void
    {
        if (PluginSettingsAccessor::getRewardPerRegistrationIsEnabled()) {
            $userHistoryEntry = new UserHistoryEntry();
            $pointsPerRegister = PluginSettingsAccessor::getRewardPerRegistrationInPoints();
            $customText = sprintf("Registration am: %s", $this->getDateFormatted());
            try {
                $currentCustomer = $this->getCurrentCustomer();
            } catch (Exception) {
                $currentCustomer = Frontend::getCustomer();
            }
            $userHistoryEntry->createEntry($pointsPerRegister, $customText, $currentCustomer->kKunde, true)->save();
        }
    }
}