<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards;

use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\cart\evaluator\CartEvaluator;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticle;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\cart\points\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginSettingsAccessor;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistoryEntry;

class CartReward extends AbstractReward
{

    /**
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

    protected function getCurrentCustomer(): Customer
    {
        // @todo check guest orders
        return Frontend::getCustomer();
    }

    public function getOrder(): Bestellung {
        return $this->getArgumentByKey("oBestellung");
    }

    public function isOrderStatusValidForProcessed(): bool {
        $currentStatus = $this->getOrder();
        $allowedStates = [
            BESTELLUNG_STATUS_BEZAHLT,
            BESTELLUNG_STATUS_VERSANDT,
            BESTELLUNG_STATUS_TEILVERSANDT
        ];
        return in_array($currentStatus, $allowedStates);
    }

    public function executeRewardLogic(): void
    {
        $evaluator = $this->getCartEvaluator();
        $orderNumber = $this->getOrder()->kBestellung;
        $orderNumberClean = $this->getOrder()->cBestellNr;
        $customText = sprintf("Bestellung am %s ausgeführt: %s (%s)", $this->getDateFormatted(), $orderNumberClean, $orderNumber);
        $this->createRewardEntry($evaluator->getAllResultPoints(), $customText);
    }


    public function setOrderStatusProcessed(): void
    {
        if($this->isOrderStatusValidForProcessed()) {
            UserHistoryEntry::setValuedAtForOrderNow($this->getOrder()->kBestellung);
        }
    }

    public function setOrderStatusCanceled(): void
    {
        UserHistoryEntry::setValuedAtForOrderNow($this->getOrder()->kBestellung, false);
    }
}