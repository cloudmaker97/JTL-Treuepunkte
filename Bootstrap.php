<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use JTL\Customer\Customer;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;
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
    public function boot(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->dispatcherListeners();
    }

    private function dispatcherListeners()
    {
        // Hook: Page in the frontend
        $this->dispatcher->listen('shop.hook.' . HOOK_SEITE_PAGE, function() {
            $this->frontendLink();
        });

        // Hook: Order status change
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, function($args) {
            $order = $args['oBestellung'];
            $orderStatus = $order->cStatus;
            if($orderStatus == BESTELLUNG_STATUS_BEZAHLT || $orderStatus == BESTELLUNG_STATUS_VERSANDT || $orderStatus == BESTELLUNG_STATUS_TEILVERSANDT  || $orderStatus == BESTELLUNG_STATUS_TEILVERSANDT) {
                // Add the points to the user
                UserHistoryEntry::setValuedAtForOrderNow($order->kBestellung);
            }
        });

        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, function($args) {
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
     */
    private function includeDebugMessagesToDOM(): void
    {
        $consoleMessage = DebugManager::outputMessagesCode();
        pq("head")->append($consoleMessage);
        DebugManager::resetMessages();
    }

    /**
     * Reward the user for a registration
     */
    private function rewardRegister($args): void
    {
        $isEnabled = $this->getPluginValue("enableRewardRegister") ?? "off";
        if ($isEnabled === "on") {
            /** @var Customer $currentUser */
            $currentUser = new Customer($args["customerID"]);
            $userHistoryEntry = new UserHistoryEntry();
            $rewardPerRegister = (int) $this->getPluginValue("rewardPerRegister") ?? 0;
            $userHistoryEntry->createEntry($rewardPerRegister, sprintf("Registration am: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
        }
    }

    /**
     * Reward the user for a visit, if the time since the last login is a while ago
     */
    private function rewardVisit(): void
    {
        $isEnabled = $this->getPluginValue("enableRewardVisit") ?? "off";
        if (Frontend::getCustomer()->isLoggedIn() && $isEnabled === "on") {
            $currentUser = Frontend::getCustomer();
            $userHistoryEntry = new UserHistoryEntry();
            $lastRewarded = new LastRewarded($currentUser);

            $timeInterval = $this->getPluginValue("rewardPerVisitCooldown") ?? "DAY";
            $timeIntervalSeconds = $this->getTimeIntervalToSeconds($timeInterval);
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getVisitAt())) {
                $rewardPerVisit = (int) $this->getPluginValue("rewardPerVisit") ?? 0;
                $userHistoryEntry->createEntry($rewardPerVisit, sprintf("Wiederholter Besuch: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
                $lastRewarded->setVisitAt()->save();
            }
        }
    }

    /**
     * Add the history to the smarty variabless
     */
    private function frontendLink(): void {
        if(Frontend::getCustomer()->isLoggedIn()) {
            $history = new UserHistory(Frontend::getCustomer());
            Shop::Smarty()->assign("dh_bonuspunkte_history", $history);
        }
    }

    /**
     * Reward the user for a login, if the time since the last login is isSecondsSinceDatePast
     */
    private function rewardLogin($args): void
    {
        $isEnabled = $this->getPluginValue("enableRewardLogin") ?? "off";
        if ($isEnabled === "on") {
            /** @var Customer $currentUser */
            $currentUser = $args["oKunde"];
            $userHistoryEntry = new UserHistoryEntry();
            $lastRewarded = new LastRewarded($currentUser);
            $timeInterval = $this->getPluginValue("rewardPerLoginCooldown") ?? "DAY";
            $timeIntervalSeconds = $this->getTimeIntervalToSeconds($timeInterval);
            if ($lastRewarded->isSecondsSinceDatePast($timeIntervalSeconds, $lastRewarded->getLoginAt())) {
                $rewardPerLogin = (int) $this->getPluginValue("rewardPerLogin") ?? 0;
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
        $pointsPieceSettingPoints = (int) $this->getPluginValue("rewardPerArticle") ?? 0;
        $pointsPiece = new PointsPerArticle();
        $pointsPiece->setDefaultPoints($pointsPieceSettingPoints);
        $evaluator->registerPointType($pointsPiece);
        // Points per article once
        $pointsOnceSettingPoints = (int) $this->getPluginValue("rewardPerArticleOnce") ?? 0;
        $pointsOnce = new PointsPerArticleOnce();
        $pointsOnce->setDefaultPoints($pointsOnceSettingPoints);
        $evaluator->registerPointType($pointsOnce);
        // Points per euro
        $pointsPerEuroSettingPoints = (int) $this->getPluginValue("rewardPerEuro") ?? 0;
        $calculateWithNetPrice = $this->getPluginValue("calculateWithNetPrice") ?? "on";
        $calculateWithNetPriceBool = $calculateWithNetPrice === "on";
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

        /** @var \JTL\Checkout\Bestellung  */
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
        switch ($timeIntervalString) {
            case "YEAR":
                return LastRewarded::SECONDS_YEAR;
            case "MONTH":
                return LastRewarded::SECONDS_MONTH;
            case "WEEK":
                return LastRewarded::SECONDS_WEEK;
            case "DAY":
                return LastRewarded::SECONDS_DAY;
            default:
                return LastRewarded::SECONDS_DAY;
        }
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