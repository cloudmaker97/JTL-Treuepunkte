<?php

namespace Plugin\dh_bonuspunkte\source\classes\points;

use JTL\Customer\Customer;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;

class LoginAbstractPoints extends AbstractPoints
{
    /**
     * @inheritDoc
     */
    public function getCurrentCustomer(): Customer {
        return $this->getArgumentByKey("oKunde");
    }

    /**
     * @inheritDoc
     */
    public function executeRewardLogic(): void
    {
        if (PluginSettingsAccessor::getRewardPerLoginIsEnabled()) {
            $lastRewarded = $this->getLastRewarded();
            $timeInterval = PluginSettingsAccessor::getRewardPerLoginCooldownOption();
            $timeIntervalSeconds = $this->getTimeIntervalToSeconds($timeInterval);
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getLoginAt())) {
                $customText = sprintf("Login am: %s", $this->getDateFormatted());
                $pointsPerLogin = PluginSettingsAccessor::getRewardPerLoginInPoints();
                $this->createRewardEntry($pointsPerLogin, $customText);
                $lastRewarded->setLoginAt()->save();
            }
        }
    }
}