<?php

namespace Plugin\dh_bonuspunkte\source\classes\frontend;

use Exception;
use JTL\Customer\Customer;
use JTL\Link\Link;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\conversion\PointsToBalanceConversion;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginInterfaceAccessor;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistory;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;

class PageController
{
    private Customer $currentCustomer;

    public function __construct()
    {
        // Load the current customer from the session
        $this->currentCustomer = Frontend::getCustomer();
        // Run page actions if it's the loyalty page
        if($this->isLoyaltyPage()) {
            $this->convertBonusPointsToBalance();
            $this->viewBonusPoints();
        }
    }

    /**
     * Get the current customer for this controller
     * @return Customer
     */
    public function getCurrentCustomer(): Customer
    {
        return $this->currentCustomer;
    }

    /**
     * Check if the current viewed page is the loyalty page by checking the frontend link url paths
     * and if the shop user is logged into his account.
     * @return bool
     */
    private function isLoyaltyPage(): bool
    {
        $requestURI = $_SERVER["REQUEST_URI"];
        $isFrontendLinkUrl = false;
        /** @var Link $singleLink */
        foreach (PluginInterfaceAccessor::getPluginInterface()->getLinks()->getLinks() as $singleLink) {
            foreach ($singleLink->getURLPaths() as $linkUrlPath) {
                if(str_contains($requestURI, $linkUrlPath)) {
                    $isFrontendLinkUrl = true;
                    break;
                }
            }
        }
        return $this->getCurrentCustomer()->isLoggedIn() && $isFrontendLinkUrl;
    }

    // The POST-Key Name for the formular: exchange widget
    private const CONVERT_POINTS_TO_BALANCE_POST_NAME = "dhBonuspointsExchangePoints";
    // The GET-Key Name for successful conversion: exchange widget
    private const CONVERT_EXCHANGE_SUCCESS_GET_PARAMETER = "exchangeSuccess";
    // The GET-Key Name for failed conversion: exchange widget
    private const CONVERT_EXCHANGE_ERROR_GET_PARAMETER = "exchangeError";

    /**
     * Converts the loyalty points to real money or prompts the user an error message
     * why the conversion failed. It also subtracts the points from the account and
     * checks for several failure reasons.
     * @return void
     */
    private function convertBonusPointsToBalance(): void {
        if(isset($_POST[static::CONVERT_POINTS_TO_BALANCE_POST_NAME])) {
            $requestedCoinsExchange = (int) ($_POST["convertPoints"]) ?? 0;
            try {
                if ($requestedCoinsExchange > 0) {
                    $pointsConverter = $this->getPointsToBalanceConverter($this->getUserPointsHistory());
                    if ($pointsConverter->isEnabled() && $pointsConverter->isWidgetActiveForUser()) {
                        if ($pointsConverter->getUnlockedPoints() >= $requestedCoinsExchange) {
                            $amountInEuro = $pointsConverter->calculatePointsToEuro($requestedCoinsExchange);
                            $customText = "Eintausch von Punkten in Shop-Guthaben";
                            $object = (new UserHistoryEntry())->createEntry(-$requestedCoinsExchange, $customText, $this->getCurrentCustomer()->kKunde, true)->save();
                            if($object->getId() != 0) {
                                $this->getCurrentCustomer()->fGuthaben += $amountInEuro;
                                $this->getCurrentCustomer()->updateInDB();
                                Shop::Smarty()->assign("alertList", Shop::Container()->getAlertService());
                                header(sprintf("Location: /bonuspunkte?%s=1", static::CONVERT_EXCHANGE_SUCCESS_GET_PARAMETER));
                                die;
                            } else {
                                throw new Exception("Diese Anzahl von Punkten konnte nicht abgezogen werden..");
                            }
                        } else {
                            throw new Exception("Diese Anzahl von Punkten hat dieses Konto nicht.");
                        }
                    } else {
                        throw new Exception("Das Modul für Umwandlung in Guthaben wurde deaktiviert.");
                    }
                } else {
                    throw new Exception("Die Anzahl von Punkten ist nicht gültig oder kleiner als 1");
                }
            } catch (Exception $exception) {
                header(sprintf("Location: /bonuspunkte?%s=%s", static::CONVERT_EXCHANGE_ERROR_GET_PARAMETER, base64_encode($exception->getMessage())));
                die;
            }
        }
    }

    /**
     * Load the smarty variables for the point-history, the conversion widget and similar
     * objects for the frontend view. The template is rendered by the frontend link, defined in the
     * shop plugin "info.xml" file. It's content directory is
     * @see /frontend/template/bonus_points.tpl
     * @return void
     */
    private function viewBonusPoints(): void
    {
        $userHistory = $this->getUserPointsHistory();
        $conversionObject = $this->getPointsToBalanceConverter($userHistory);

        $variableLoader = [
            "dh_bonuspunkte_history" => $userHistory,
            "dh_bonuspunkte_unlock_days" => PluginSettingsAccessor::getRewardUnlockAfterDays(),
            "dh_bonuspunkte_conversion" => $conversionObject,
            "dh_bonuspunkte_form_name" => static::CONVERT_POINTS_TO_BALANCE_POST_NAME,
            "dh_bonuspunkte_form_success" => isset($_GET[self::CONVERT_EXCHANGE_SUCCESS_GET_PARAMETER]),
            "dh_bonuspunkte_form_error" => isset($_GET[self::CONVERT_EXCHANGE_ERROR_GET_PARAMETER]) ? base64_decode($_GET[self::CONVERT_EXCHANGE_ERROR_GET_PARAMETER]) : false,
        ];

        foreach ($variableLoader as $key => $value) {
            Shop::Smarty()->assign($key, $value);
        }
    }

    /**
     * @param UserHistory $userHistory Is used for calculating the current amount of available points
     * @return PointsToBalanceConversion
     */
    public function getPointsToBalanceConverter(UserHistory $userHistory): PointsToBalanceConversion
    {
        $conversionObject = new PointsToBalanceConversion();
        $conversionObject->setIsEnabled(PluginSettingsAccessor::getConversionToShopBalanceIsEnabled());
        $conversionObject->setMinimumTradeIn(PluginSettingsAccessor::getConversionMinimumPointsTradeIn());
        $conversionObject->setPointsForOneEuro(PluginSettingsAccessor::getConversionRateForEachEuroInPoints());
        $conversionObject->setUnlockedPoints($userHistory->getTotalValuedPoints());
        $conversionObject->setCustomer(Frontend::getCustomer());
        return $conversionObject;
    }

    /**
     * @return UserHistory
     */
    public function getUserPointsHistory(): UserHistory
    {
        return new UserHistory($this->getCurrentCustomer());
    }
}