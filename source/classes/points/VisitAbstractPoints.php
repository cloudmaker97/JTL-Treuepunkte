<?php

namespace Plugin\dh_bonuspunkte\source\classes\points;

use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;

class VisitAbstractPoints extends AbstractPoints
{
    /**
     * @inheritDoc
     */
    protected function getCurrentCustomer(): Customer
    {
        return Frontend::getCustomer();
    }

    /**
     * @inheritDoc
     */
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