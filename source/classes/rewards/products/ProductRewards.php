<?php

namespace Plugin\dh_bonuspunkte\source\classes\rewards\products;

use JTL\Catalog\Product\Artikel;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\history\UserHistory;

class ProductRewards
{
    private const BONUSPUNKTE_REWARD_IN_POINTS = "bonuspunkte_reward_in_points";

    /**
     * Update a product after it's loaded in the shop. It will check the product for the existence of
     * a functional attribute. If the attribute is available, the product will be modified in price (free)
     * and it retrieves a special description.
     * @param Artikel $product
     * @return void
     */
    public function updateProductAfterLoaded(Artikel $product): void
    {
        if(!isset($product->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS])) return;
        $pointValue = intval($product->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS]);
        if($pointValue > 0) {
            $product->Preise->setPricesToZero();
            $product->Preise->localizePreise();
            $product->cKurzBeschreibung .= $this->getDescriptionBadge($pointValue);
        }
    }

    /**
     * @return void
     */
    public function updateCartPositionsForRewardProducts(): void
    {
        $currentCart = Frontend::getCart();
        foreach ($currentCart->PositionenArr as $cartItem) {
            if (!isset($cartItem->Artikel->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS])) continue;
            $pointValue = intval($cartItem->Artikel->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS]);
            $pointValue = $pointValue * $cartItem->nAnzahl;
            if ($pointValue > 0) {
                $cartItem->fPreis = 0;
                $cartItem->fPreisEinzelNetto = 0;
                $cartItem->setzeGesamtpreisLocalized();
                $cartItem->Artikel->cKurzBeschreibung .= $this->getDescriptionBadge($pointValue);
            }
        }
    }

    /**
     * @return void
     */
    public function reloadPageAfterCartChange(): void
    {
        if(isset($_POST['anzahl'])) {
            header("Location: ?v=2");
            die;
        }
    }

    public function getMissingPointsForProductRedeem(): int
    {
        $currentHistory = new UserHistory(Frontend::getCustomer());
        $currentCart = Frontend::getCart();
        $allNeededPoints = 0;
        foreach ($currentCart->PositionenArr as $cartItem) {
            if (!isset($cartItem->Artikel->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS])) continue;
            $pointValue = intval($cartItem->Artikel->FunktionsAttribute[self::BONUSPUNKTE_REWARD_IN_POINTS]);
            $pointValue = $pointValue * $cartItem->nAnzahl;
            $allNeededPoints += $pointValue;
        }

        if($currentHistory->getTotalValuedPoints() <= 0 || $currentHistory->getTotalValuedPoints() < $allNeededPoints) {
            return $allNeededPoints - $currentHistory->getTotalValuedPoints();
        }
        return 0;
    }

    /**
     * @param int $amountOfPoints
     * @return string
     */
    private function getDescriptionBadge(int $amountOfPoints): string
    {
        return sprintf("<div class='bg-neutral-100 border !border-neutral-300 rounded-sm p-1 mb-1 mt-1'>Eintauschbar für insgesamt %d Treuepunkte</div>", $amountOfPoints);
    }
}