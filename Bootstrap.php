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
use Plugin\dh_bonuspunkte\source\classes\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;
use Plugin\dh_bonuspunkte\source\classes\history\LastRewarded;
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
     * @todo Make rewards configurable
     */
    private function rewardRegister($args)
    {
        /** @var Customer $currentUser */
        $currentUser = new Customer($args["customerID"]);
        $userHistoryEntry = new UserHistoryEntry();
        $userHistoryEntry->createEntry(0, sprintf("Registration am: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
    }

    /**
     * Reward the user for a visit, if the time since the last login is isSecondsSinceDatePast
     * @todo Make time and rewards configurable
     */
    private function rewardVisit()
    {
        if(Frontend::getCustomer()->isLoggedIn()) {
            $currentUser = Frontend::getCustomer();
            $userHistoryEntry = new UserHistoryEntry();
            $lastRewarded = new LastRewarded($currentUser);
            
            if($lastRewarded->isSecondsSinceDatePast(LastRewarded::SECONDS_DAY, $lastRewarded->getVisitAt())) {
                $userHistoryEntry->createEntry(0, sprintf("Wiederholter Besuch: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
                $lastRewarded->setVisitAt()->save();
            }
        }
    }

    /**
     * Reward the user for a login, if the time since the last login is isSecondsSinceDatePast
     * @todo Make time and rewards configurable
     */
    private function rewardLogin($args)
    {
        /** @var Customer $currentUser */
        $currentUser = $args["oKunde"];
        $userHistoryEntry = new UserHistoryEntry();
        $lastRewarded = new LastRewarded($currentUser);
        
        if($lastRewarded->isSecondsSinceDatePast(LastRewarded::SECONDS_DAY, $lastRewarded->getLoginAt())) {
            $userHistoryEntry->createEntry(0, sprintf("Login am: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
            $lastRewarded->setLoginAt()->save();
        }
    }

    /**
     * Adding the points for cart purchases to the temp bonus points storage
     * @todo Make rewards configurable and tax setting
     */
    private function rewardCart($args): void
    {
        $evaluator = new CartEvaluator();
        // Points per article
        $pointsPiece = new PointsPerArticle();
        $pointsPiece->setDefaultPoints(0); 
        $evaluator->registerPointType($pointsPiece);
        // Points per article once
        $pointsOnce = new PointsPerArticleOnce();
        $pointsOnce->setDefaultPoints(0); 
        $evaluator->registerPointType($pointsOnce);
        // Points per euro
        $pointsPerEuro = new PointsPerEuro();
        $pointsPerEuro->setTaxSetting(false); 
        $pointsPerEuro->setDefaultPoints(0); 
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
}