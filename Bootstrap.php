<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use Plugin\dh_bonuspunkte\source\classes\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;

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
        $this->dispatcher->listen('shop.hook.'.HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB, function() {
            DebugManager::addMessage(new DebugMessage("Cart triggered"));
            $this->rewardCart();
        });

        // Hook: Account Login
        $this->dispatcher->listen('shop.hook.'.HOOK_KUNDE_CLASS_HOLLOGINKUNDE, function($args) {
            DebugManager::addMessage(new DebugMessage("Login triggered"));
            $this->rewardLogin();
        });

        // Hook: Registration
        $this->dispatcher->listen('shop.hook.'.HOOK_REGISTRATION_CUSTOMER_CREATED, function($args) {
            DebugManager::addMessage(new DebugMessage("Registration triggered"));
            $this->rewardRegister();
        });

        // Listener before smarty include: Add the debug messages to the DOM
        $this->dispatcher->listen('shop.hook.'.HOOK_SMARTY_OUTPUTFILTER, function() {
            $this->includeDebugMessagesToDOM();
        });
    }

    /**
     * Add the debug messages to the DOM before the smarty template is rendered,
     * because the PHPQuery DOM is not available anymore after the smarty template is rendered
     */
    private function includeDebugMessagesToDOM(): void {
        $consoleMessage = DebugManager::outputMessagesCode();
        pq("head")->append($consoleMessage);
        DebugManager::resetMessages();
    }


    private function rewardRegister()
    {
        // @todo only once per customer, check via database
        // and only if set in the plugins settings
    }

    private function rewardLogin()
    {
        
        // @todo only daily, check via database
        // and only if the period is set in the plugins settings
    }

    /**
     * Adding the points for cart purchases to the temp bonus points storage
     */
    private function rewardCart(): void {
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
        // @todo withdraw points from customer account
        // but: only if the order is paid or its delivery status is "shipped"
    }
}
