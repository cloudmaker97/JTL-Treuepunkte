<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use Exception;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\conversion\PointsToBalanceConversion;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\LastRewarded;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistory;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;

/**
 * Class Bootstrap
 * @package Plugin\dh_bonuspunkte
 */
class Bootstrap extends Bootstrapper
{
    /**
     * The event dispatcher from the JTL Shop
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * @inheritDoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        global $pluginInterfaceForDhBonuspoints;
        $pluginInterfaceForDhBonuspoints = $this->getPlugin();

        $this->dispatcher = $dispatcher;
        $this->dispatcherListeners();
    }

    /**
     * Register the event listeners for the plugin,
     * so that the plugin can react to the events of the shop
     */
    private function dispatcherListeners(): void
    {
        // Hook: Page in the frontend is loaded
        $this->dispatcher->listen('shop.hook.' . HOOK_SEITE_PAGE, function () {
            // Inject the webpack script inline to the head of the page
            $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function () {
                $this->injectWebpackScriptInline();
            });
            $this->pageFrontendLink();
        });

        // Hook: Order status change
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, function ($args) {
            $order = $args['oBestellung'];
            $orderStatus = $order->cStatus;
            if ($orderStatus == BESTELLUNG_STATUS_BEZAHLT || $orderStatus == BESTELLUNG_STATUS_VERSANDT || $orderStatus == BESTELLUNG_STATUS_TEILVERSANDT) {
                // Add the points to the user
                UserHistoryEntry::setValuedAtForOrderNow($order->kBestellung);
            }
        });

        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, function ($args) {
            $order = $args['oBestellung'];
            UserHistoryEntry::setValuedAtForOrderNow($order->kBestellung, false);
        });

        // Hook: Cart finalization
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, function ($args) {
            $this->rewardCart($args);
        });

        // Hook: Account Login
        $this->dispatcher->listen('shop.hook.' . HOOK_KUNDE_CLASS_HOLLOGINKUNDE, function ($args) {
            $this->rewardLogin($args);
        });

        // Hook: Registration
        $this->dispatcher->listen('shop.hook.' . HOOK_REGISTRATION_CUSTOMER_CREATED, function ($args) {
            $this->rewardRegister($args);
        });

        // Hook: Each visit, before the smarty template is rendered
        $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function () {
            $this->rewardVisit();
            $this->includeDebugMessagesToDOM();
        });
    }

    /**
     * Add the debug messages to the DOM before the smarty template is rendered,
     * because the PHPQuery DOM is not available anymore after the smarty template is rendered
     * @throws Exception
     */
    private function includeDebugMessagesToDOM(): void
    {
        $consoleMessage = DebugManager::outputMessagesCode();
        pq("head")->append($consoleMessage);
        DebugManager::resetMessages();
    }

    /**
     * Inject the webpack script inline to the head of the page,
     * otherwise the Shop will always load the script from the plugin, even if the script is not used
     * This is a workaround to optimize / maintain the performance of the shop
     * @throws Exception
     */
    private function injectWebpackScriptInline(): void
    {
        $scriptElement = sprintf("<script>%s</script>", file_get_contents(__DIR__ . "/frontend/js/main.js"));
        pq("head")->append($scriptElement);
    }

    /**
     * Reward the user for a registration
     */
    private function rewardRegister($args): void
    {
        if (PluginSettingsAccessor::getRewardPerRegistrationIsEnabled()) {
            $currentUser = new Customer($args["customerID"]);
            $userHistoryEntry = new UserHistoryEntry();
            $rewardPerRegister = PluginSettingsAccessor::getRewardPerRegistrationInPoints();
            $userHistoryEntry->createEntry($rewardPerRegister, sprintf("Registration am: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
        }
    }

    /**
     * Reward the user for a visit, if the time since the last login is a while ago
     */
    private function rewardVisit(): void
    {
        if (Frontend::getCustomer()->isLoggedIn() && PluginSettingsAccessor::getRewardPerVisitIsEnabled()) {
            $currentUser = Frontend::getCustomer();
            $userHistoryEntry = new UserHistoryEntry();
            $lastRewarded = new LastRewarded($currentUser);

            $timeIntervalSeconds = $this->getTimeIntervalToSeconds(PluginSettingsAccessor::getRewardPerVisitCooldownOption());
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getVisitAt())) {
                $rewardPerVisit = PluginSettingsAccessor::getRewardPerVisitInPoints();
                $userHistoryEntry->createEntry($rewardPerVisit, sprintf("Wiederholter Besuch: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
                $lastRewarded->setVisitAt()->save();
            }
        }
    }

    /**
     * Add the history to the smarty variables
     */
    private function pageFrontendLink(): void
    {
        if (Frontend::getCustomer()->isLoggedIn()) {
            $userHistory = new UserHistory(Frontend::getCustomer());
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

    /**
     * Reward the user for a login, if the time since the last login is isSecondsSinceDatePast
     */
    private function rewardLogin($args): void
    {
        if (PluginSettingsAccessor::getRewardPerLoginIsEnabled()) {
            /** @var Customer $currentUser */
            $currentUser = $args["oKunde"];
            $userHistoryEntry = new UserHistoryEntry();
            $lastRewarded = new LastRewarded($currentUser);
            $timeInterval = PluginSettingsAccessor::getRewardPerLoginCooldownOption();
            $timeIntervalSeconds = $this->getTimeIntervalToSeconds($timeInterval);
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getLoginAt())) {
                $rewardPerLogin = PluginSettingsAccessor::getRewardPerLoginInPoints();
                $userHistoryEntry->createEntry($rewardPerLogin, sprintf("Login am: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
                $lastRewarded->setLoginAt()->save();
            }
        }
    }

    /**
     * Adding the points for cart purchases to the temp bonus points storage
     */
    private function rewardCart($args): void
    {
        $evaluator = new CartEvaluator();
        // Points per article
        $pointsPieceSettingPoints = PluginSettingsAccessor::getRewardPerArticleByDefault();
        $pointsPiece = new PointsPerArticle();
        $pointsPiece->setDefaultPoints($pointsPieceSettingPoints);
        $evaluator->registerPointType($pointsPiece);
        // Points per article once
        $pointsOnceSettingPoints = PluginSettingsAccessor::getRewardPerArticleOnceByDefault();
        $pointsOnce = new PointsPerArticleOnce();
        $pointsOnce->setDefaultPoints($pointsOnceSettingPoints);
        $evaluator->registerPointType($pointsOnce);
        // Points per euro
        $pointsPerEuroSettingPoints = PluginSettingsAccessor::getRewardPerValueEachEuroByDefault();
        $calculateWithNetPriceBool = PluginSettingsAccessor::getRewardPerValueEachEuroInNetPrices();
        $pointsPerEuro = new PointsPerEuro();
        $pointsPerEuro->setTaxSetting($calculateWithNetPriceBool);
        $pointsPerEuro->setDefaultPoints($pointsPerEuroSettingPoints);
        $evaluator->registerPointType($pointsPerEuro);
        // Evaluate the points
        $evaluator->evaluatePoints();
        DebugManager::addMessage(new DebugMessage("Cart: ", [
            "points" => $evaluator->getAllResultPoints(),
            "data" => $evaluator->getAllResultObjects(),
        ]));

        /** @var Bestellung $args */
        $currentOrder = $args["oBestellung"];
        $currentUser = Frontend::getCustomer();
        $orderNumber = $currentOrder->kBestellung;
        $orderNumberClean = $currentOrder->cBestellNr;
        $userHistoryEntry = new UserHistoryEntry();
        $userHistoryEntry->createEntry($evaluator->getAllResultPoints(), sprintf("Bestellung ausgeführt: %s (%s)", $orderNumberClean, $orderNumber), $currentUser->kKunde, false, $orderNumber)->save();
    }


    /**
     * Get the seconds of the given time interval string and after a match
     * get the corresponding constant from the LastRewarded class
     */
    private function getTimeIntervalToSeconds(string $timeIntervalString): int
    {
        return match ($timeIntervalString) {
            "YEAR" => LastRewarded::SECONDS_YEAR,
            "MONTH" => LastRewarded::SECONDS_MONTH,
            "WEEK" => LastRewarded::SECONDS_WEEK,
            default => LastRewarded::SECONDS_DAY,
        };
    }

    /**
     * Get the plugin setting value of the given setting name
     * @param string $settingName The name of the setting
     */
    private function getPluginValue(string $settingName): mixed
    {
        return $this->getPlugin()->getConfig()->getValue($settingName);
    }
}