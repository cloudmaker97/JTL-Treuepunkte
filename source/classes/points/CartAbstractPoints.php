<?php

namespace Plugin\dh_bonuspunkte\source\classes\points;

use Exception;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;
use Plugin\dh_bonuspunkte\source\classes\points\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\points\cart\types\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\points\cart\types\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\points\cart\types\PointsPerEuro;
use stdClass;

class CartAbstractPoints extends AbstractPoints
{

    /**
     * Add the module for: Points per article
     * @param CartEvaluator $evaluator
     * @return void
     */
    private function addEvaluator_perArticle(CartEvaluator $evaluator): void
    {
        $pointsPieceSettingPoints = PluginSettingsAccessor::getRewardPerArticleByDefault();
        $pointsPiece = new PointsPerArticle();
        $pointsPiece->setDefaultPoints($pointsPieceSettingPoints);
        $evaluator->registerPointType($pointsPiece);
    }

    /**
     * Add the module for: Points per article (once)
     * @param CartEvaluator $evaluator
     * @return void
     */
    private function addEvaluator_perArticleOnce(CartEvaluator $evaluator): void
    {
        $pointsOnceSettingPoints = PluginSettingsAccessor::getRewardPerArticleOnceByDefault();
        $pointsOnce = new PointsPerArticleOnce();
        $pointsOnce->setDefaultPoints($pointsOnceSettingPoints);
        $evaluator->registerPointType($pointsOnce);
    }

    /**
     * Add the module for: Points per Euro
     * @param CartEvaluator $evaluator
     * @return void
     */
    private function addEvaluator_perEuro(CartEvaluator $evaluator): void
    {
        $pointsPerEuroSettingPoints = PluginSettingsAccessor::getRewardPerValueEachEuroByDefault();
        $calculateWithNetPriceBool = PluginSettingsAccessor::getRewardPerValueEachEuroInNetPrices();
        $pointsPerEuro = new PointsPerEuro();
        $pointsPerEuro->setTaxSetting($calculateWithNetPriceBool);
        $pointsPerEuro->setDefaultPoints($pointsPerEuroSettingPoints);
        $evaluator->registerPointType($pointsPerEuro);
    }

    /**
     * Get a evaluator for the cart with the initialized modules
     * @return CartEvaluator
     */
    private function getCartEvaluator(): CartEvaluator
    {
        $evaluator = new CartEvaluator();
        $this->addEvaluator_perArticle($evaluator);
        $this->addEvaluator_perArticleOnce($evaluator);
        $this->addEvaluator_perEuro($evaluator);
        $evaluator->evaluatePoints();
        return $evaluator;
    }

    /**
     * Get the current customer, if no customer account exists the shop creates
     * a new customer with the guest customer data.
     * @return Customer
     */
    protected function getCurrentCustomer(): Customer
    {
        return Frontend::getCustomer();
    }

    /**
     * Get the current order from the passed arguments
     * @return Bestellung
     * @throws Exception
     */
    public function getOrder(): Bestellung {

        $fromAttribute = $this->getArgumentByKey("oBestellung");
        if($fromAttribute instanceof Bestellung) {
            return $fromAttribute;
        } elseif ($fromAttribute instanceof stdClass) {
            return new Bestellung($fromAttribute->kBestellung);
        } else {
            throw new Exception("Error while loading order");
        }
    }

    /**
     * Get if the order status contains a valid status for being stated
     * as delivered, paid or partly delivered.
     * @return bool
     */
    public function isOrderStatusValidForProcessed(): bool {
        try {
            $currentStatus = $this->getOrder();
            $allowedStates = [
                BESTELLUNG_STATUS_BEZAHLT,
                BESTELLUNG_STATUS_VERSANDT,
                BESTELLUNG_STATUS_TEILVERSANDT
            ];
            return in_array($currentStatus, $allowedStates);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function executeRewardLogic(): void
    {
        $evaluator = $this->getCartEvaluator();
        $orderNumber = $this->getOrder()->kBestellung;
        $orderNumberClean = $this->getOrder()->cBestellNr;
        // Only withdraw points if the cart is paid totally by the user
        if(!$this->getOrderIsPaidWithBalance()) {
            $customText = sprintf("Bestellung am %s ausgeführt: %s (%s)", $this->getDateFormatted(), $orderNumberClean, $orderNumber);
            $this->createRewardEntry($evaluator->getAllResultPoints(), $customText);
        }
    }

    /**
     * Get if the order is paid with shop balance, this is always the case
     * if the fGuthaben value of the order is below zero.
     * @return bool
     */
    private function getOrderIsPaidWithBalance(): bool
    {
        return $this->getOrder()->fGuthaben < 0;
    }

    /**
     * Set that the order status has been updated
     * @return void
     */
    public function setOrderStatusProcessed(): void
    {
        if($this->isOrderStatusValidForProcessed()) {
            UserHistoryEntry::setValuedAtForOrderNow($this->getOrder()->kBestellung);
        }
    }

    /**
     * Set that the order has been canceled by user or shop owner
     * @return void
     */
    public function setOrderStatusCanceled(): void
    {
        UserHistoryEntry::setValuedAtForOrderNow($this->getOrder()->kBestellung, false);
    }
}