<?php
namespace Plugin\dh_bonuspunkte\source\classes\points\cart\types;

use JTL\Cart\CartItem;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;

/**
 * This points class is used to calculate the points based on the functional attribute "bonuspunkte_pro_artikel"
 * Example: Article A is set to 2 points, there are 3 of them in the cart, the result is 6 points.
 * Each article gives the same amount of points, set in the functional attribute.
 */
class PointsPerArticle implements IPoint {
	/**
	 * @var int $defaultPoints The default amount of points if the functional attribute is not set
	 */
	private int $defaultPoints = 0;

	/**
	 * With this method you can set the default amount of points if the functional attribute is not set
	 */
	public function setDefaultPoints(int $defaultPoints): void
	{
		$this->defaultPoints = $defaultPoints;
	}

    /**
	 * @inheritDoc
	 */
	public static function getName(): string {
        return "Bonuspunkte pro Artikel";
	}

    /**
     * Get the functional attribute name
     * @return string
     */
    public static function getFunctionAttributName(): string {
        return "bonuspunkte_pro_artikel";
	}
	
    /**
	 * @inheritDoc
     * @param CartItem $data
	 */
	public function getPointAmount($data): int {
        if($data->nPosTyp !== C_WARENKORBPOS_TYP_ARTIKEL) return 0;
		if(isset($data->Artikel->FunktionsAttribute[self::getFunctionAttributName()])) {
			return $data->Artikel->FunktionsAttribute[self::getFunctionAttributName()] * $data->nAnzahl;
		} else {
			return $this->defaultPoints;
		}
	}
}