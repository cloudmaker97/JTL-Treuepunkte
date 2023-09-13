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
            DebugManager::addMessage(new DebugMessage("Registration triggered"));
            $this->rewardRegister($args);
        });

        // Listener before smarty include: Add the debug messages to the DOM
        $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function () {
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


    private function rewardRegister($args)
    {
        /** @var Customer $currentUser */
        $currentUser = new Customer($args["customerID"]);
        $userHistoryEntry = new UserHistoryEntry();
        $userHistoryEntry->createEntry(0, sprintf("Registration: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
        // @todo and only if amount set in the plugins settings
        // @todo only once per customer, check via database
    }

    private function rewardLogin($args)
    {
        /** @var Customer $currentUser */
        $currentUser = $args["oKunde"];
        $userHistoryEntry = new UserHistoryEntry();
        $userHistoryEntry->createEntry(0, sprintf("Login: %s", (new \DateTime())->format("d.m.Y")), $currentUser->kKunde, true)->save();
        // @todo and only if amount set in the plugins settings
        // @todo only once per customer, check via database
    }

    /**
     * Adding the points for cart purchases to the temp bonus points storage
     */
    private function rewardCart($args): void
    {
        $evaluator = new CartEvaluator();
        // Points per article
        $pointsPiece = new PointsPerArticle();
        $pointsPiece->setDefaultPoints(0); // @todo make this configurable
        $evaluator->registerPointType($pointsPiece);
        // Points per article once
        $pointsOnce = new PointsPerArticleOnce();
        $pointsOnce->setDefaultPoints(0); // @todo make this configurable
        $evaluator->registerPointType($pointsOnce);
        // Points per euro
        $pointsPerEuro = new PointsPerEuro();
        $pointsPerEuro->setTaxSetting(false); // @todo make this configurable
        $pointsPerEuro->setDefaultPoints(0); // @todo make this configurable
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
        $userHistoryEntry->createEntry($evaluator->getAllResultPoints(), sprintf("Bestellung: %s (%s)", $orderNumberClean, $orderNumber), $currentUser->kKunde, false, $orderNumber)->save();
    }
}