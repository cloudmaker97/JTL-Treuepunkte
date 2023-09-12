<?php
namespace Plugin\dh_bonuspunkte\source\classes\points\cart;

use JTL\Cart\CartItem;
use Plugin\dh_bonuspunkte\source\classes\points\types\CartType;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPointType;

class PointsPerEuro implements IPoint {
	private const GET_PRICE_IN_NETTO = true;


    /**
	 * @inheritDoc
	 */
	public static function getName(): string {
        return "Bonuspunkte pro Euro";
	}
	
    /**
	 * @inheritDoc
	 */
	public static function getFunctionAttributName(): string {
        return "bonuspunkte_pro_euro";
	}
	
    /**
	 * @inheritDoc
	 */
	public function getPointAmount(CartItem $position): int {
		if(isset($position->Artikel->FunktionsAttribute[self::getFunctionAttributName()])) {

			$allArticlePrice = round($position->fVK[static::GET_PRICE_IN_NETTO] * $position->nAnzahl);
			$pointsFromEuro = $allArticlePrice * $position->Artikel->FunktionsAttribute[self::getFunctionAttributName()];

			return $pointsFromEuro;
		}
		return 0;
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): IPointType {
		return new CartType();
	}
}