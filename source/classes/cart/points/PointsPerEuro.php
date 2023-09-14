<?php
namespace Plugin\dh_bonuspunkte\source\classes\cart\points;

use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;

/**
 * PointsPerEuro: Retrieves the amount of points based on the buy price
 * Example: Article A is set to 2 points, there are 3 of them in the cart, the result is 2 points
 */
class PointsPerEuro implements IPoint {
	/** 
	 * @var bool $isRespectingPriceNetto If true, the net price is used, otherwise the gross price 
	 */
	private bool $isNetPrice = false;

	/**
	 * @var int $defaultPoints The default amount of points if the functional attribute is not set
	 */
	private int $defaultPoints = 0;

	/**
	 * With this method you can set if the netto or brutto price should be used.
	 * This is used for the calculation of the points, by default the brutto price is used.
	 * @param bool $isNetPrice
	 */
	public function setTaxSetting(bool $isNetPrice): void
	{
		$this->isNetPrice = $isNetPrice;
	}

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
        return "Bonuspunkte pro Euro";
	}

    /**
     * Get the functional attribute name
     * @return string
     */
    public static function getFunctionAttributName(): string {
        return "bonuspunkte_pro_euro";
	}
	
    /**
	 * @inheritDoc
	 */
	public function getPointAmount($data): int {
		if($data->fVK == null) return 0;
		$price = $data->fVK[$this->isNetPrice];
		$allArticlePrice = round($price * $data->nAnzahl);
		if(isset($data->Artikel->FunktionsAttribute[self::getFunctionAttributName()])) {
            return $allArticlePrice * $data->Artikel->FunktionsAttribute[self::getFunctionAttributName()];
		} else {
            return $allArticlePrice * $this->defaultPoints;
		}
	}
}