<?php
namespace Plugin\dh_bonuspunkte\source\interfaces\points;

interface IPoint
{
    /**
     * Returns the name of this type
     * @return string The name of this type
     */
    public static function getName(): string;

    /**
     * Returns the amount of points for this type based on the given data
     * @return int The amount of points for this type
     */
    public function getPointAmount($data): int;
}