<?php
namespace Plugin\dh_bonuspunkte\source\classes\points\types;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPointType;

class CartType implements IPointType {
	public const TYPE_NAME = "cart";

	/**
     * @inheritDoc
	 */
	public function getName() {
        return self::TYPE_NAME;
	}
}