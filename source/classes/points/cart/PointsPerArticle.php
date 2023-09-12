<?php
namespace Plugin\dh_bonuspunkte\source\classes\points\cart;

use JTL\Cart\CartItem;
use Plugin\dh_bonuspunkte\source\classes\points\types\CartType;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPointType;


class PointsPerArticle implements IPoint {

    /**
	 * @inheritDoc
	 */
	public static function getName(): string {
        return "Bonuspunkte pro Artikel";
	}
	
    /**
	 * @inheritDoc
	 */
	public static function getFunctionAttributName(): string {
        return "bonuspunkte_pro_artikel";
	}
	
    /**
	 * @inheritDoc
	 */
	public function getPointAmount(CartItem $position): int {
		if(isset($position->Artikel->FunktionsAttribute[self::getFunctionAttributName()])) {
			return $position->Artikel->FunktionsAttribute[self::getFunctionAttributName()] * $position->nAnzahl;
		} else {
			return 0;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): IPointType {
		return new CartType();
	}
}