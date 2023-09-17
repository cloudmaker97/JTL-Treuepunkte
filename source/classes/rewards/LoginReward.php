<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards;

use JTL\Customer\Customer;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;

class LoginReward extends AbstractReward
{
    public function getCurrentCustomer(): Customer {
        return $this->getArgumentByKey("oKunde");
    }

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