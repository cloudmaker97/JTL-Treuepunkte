<?php

namespace Plugin\dh_bonuspunkte\source\classes\frontend;

use JTL\Customer\Customer;
use JTL\Link\Link;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\conversion\PointsToBalanceConversion;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginInterfaceAccessor;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistory;

class PageController
{
    private Customer $currentCustomer;

    public function __construct()
    {
        $this->currentCustomer = Frontend::getCustomer();

        if($this->isLoyaltyPage()) {
            $this->convertBonusPointsToBalance();
            $this->viewBonusPoints();
        }
    }

    public function getCurrentCustomer(): Customer
    {
        return $this->currentCustomer;
    }

    /**
     *
     * @return bool
     */
    private function isLoyaltyPage(): bool
    {
        $requestURI = $_SERVER["REQUEST_URI"];
        $isFrontendLinkUrl = false;
        /** @var Link $singleLink */
        foreach (PluginInterfaceAccessor::getPluginInterface()->getLinks()->getLinks() as $singleLink) {
            foreach ($singleLink->getURLPaths() as $linkUrlPath) {
                if($linkUrlPath == $requestURI) {
                    $isFrontendLinkUrl = true;
                    break;
                }
            }
        }
        return $this->getCurrentCustomer()->isLoggedIn() && $isFrontendLinkUrl;
    }

    private function convertBonusPointsToBalance(): void {
    }

    /**
     * @return void
     */
    private function viewBonusPoints(): void
    {
        $userHistory = new UserHistory($this->currentCustomer);
        $conversionObject = new PointsToBalanceConversion();
        $conversionObject->setIsEnabled(PluginSettingsAccessor::getConversionToShopBalanceIsEnabled());
        $conversionObject->setMinimumTradeIn(PluginSettingsAccessor::getConversionMinimumPointsTradeIn());
        $conversionObject->setPointsForOneEuro(PluginSettingsAccessor::getConversionRateForEachEuroInPoints());
        $conversionObject->setUnlockedPoints($userHistory->getTotalValuedPoints());
        $conversionObject->setCustomer(Frontend::getCustomer());

        $variables = [
            "dh_bonuspunkte_history" => $userHistory,
            "dh_bonuspunkte_unlock_days" => PluginSettingsAccessor::getRewardUnlockAfterDays(),
            "dh_bonuspunkte_conversion" => $conversionObject,
        ];
        foreach ($variables as $key => $value) {
            Shop::Smarty()->assign($key, $value);
        }
    }
}