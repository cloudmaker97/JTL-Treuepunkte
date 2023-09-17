<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards;

use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;

class VisitReward extends AbstractReward
{

    protected function getCurrentCustomer(): Customer
    {
        return Frontend::getCustomer();
    }

    public function executeRewardLogic(): void
    {
        if ($this->getCurrentCustomer()->isLoggedIn() && PluginSettingsAccessor::getRewardPerVisitIsEnabled()) {
            $lastRewarded = $this->getLastRewarded();
            $timeIntervalSeconds = $this->getTimeIntervalToSeconds(PluginSettingsAccessor::getRewardPerVisitCooldownOption());
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getVisitAt())) {
                $pointsPerVisit = PluginSettingsAccessor::getRewardPerVisitInPoints();
                $customText = sprintf("Wiederholter Besuch: %s", $this->getDateFormatted());
                $this->createRewardEntry($pointsPerVisit, $customText);
            }
        }
    }
}