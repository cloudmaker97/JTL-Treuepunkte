<?php
namespace Plugin\dh_bonuspunkte\source\classes\cart\evaluator;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;

/**
 * Represents a single point result
 * for further processing
 */
class PointResult {
    /**
     * @var IPoint The point type of this result
     */
    private IPoint $pointType; 

    /**
     * @var int The amount of points for this result
     */
    private int $pointAmount;

    /**
     * Creates a new point result
     * @param IPoint $pointType The point type of this result
     * @param int $pointAmount The amount of points for this result
     */
    public function __construct(IPoint $pointType, int $pointAmount = 0)
    {
        $this->pointType = $pointType;
        $this->pointAmount = $pointAmount;
    }

    /**
     * Returns the point amount of this result
     * @return int The point amount of this result
     */
    public function getAmount(): int
    {
        return $this->pointAmount;
    }
}