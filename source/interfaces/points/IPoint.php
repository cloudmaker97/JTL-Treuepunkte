<?php
namespace Plugin\dh_bonuspunkte\source\interfaces\points;
use JTL\Cart\CartItem;

interface IPoint
{
    /**
     * Returns the type of this point. It is used to group various income types together.
     * For example, the type "cart" is used for all points that are earned by the cart.
     * Or the type "user" is for all points that are earned by affiliate registrations.
     * @return IPointType The type of this point
     */
    public function getType(): IPointType;

    /**
     * Returns the name of this type
     * @return string The name of this type
     */
    public static function getName(): string;

    /**
     * Returns the name of the function attribute
     * @return string The name of the function attribute
     */
    public static function getFunctionAttributName(): string;

    /**
     * Returns the amount of points for this type
     * @return int The amount of points for this type
     */
    public function getPointAmount(CartItem $position): int;
}