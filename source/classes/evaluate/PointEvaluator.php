<?php
namespace Plugin\dh_bonuspunkte\source\classes\evaluate;
use JTL\Cart\CartItem;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\points\types\CartType;
use Plugin\dh_bonuspunkte\source\interfaces\points\IPoint;

class PointEvaluator {
    /**
     * This array contains all registered point types for evaulation
     * @var IPoint[]
     */
    private $pointTypes = [];

    /**
     * This array contains all results of the evaluation. Each point type
     * has its own result object for storing the amount of points.
     * @var PointResult[]
     */
    private $pointResults = [];

    /**
     * Iterate over all registered point types and evaluate the points
     */
    public function evaluatePoints(): void {
        // Reset the point results
        $this->pointResults = [];
        // Iterate over all point types
        foreach($this->getPointTypes() as $pointType) {
            // If the point type is a cart type, evaluate the cart positions
            if($pointType->getType()->getName() == CartType::TYPE_NAME) {
                $positions = $this->getCartPositions();
                foreach ($positions as $position) {
                    $points = $pointType->getPointAmount($position);
                    $pointResult = new PointResult($pointType, $points);
                    $this->pointResults[] = $pointResult;
                }
            }
        }
    }

    /**
     * Returns the amount of points for all point types
     * @return int The amount of points for all point types
     */
    public function getAllResultPoints(): int
    {
        $amountOfPoints = 0;
        foreach($this->pointResults as $pointResult) {
            $amountOfPoints += $pointResult->getAmount();
        }
        return $amountOfPoints;
    }

    /**
     * Returns the result objects for all point types
     * @return PointResult[] The result objects for all point types
     */
    public function getAllResultObjects(): array
    {
        return $this->pointResults;
    }

    /**
     * Register a point type for this evaluator
     * @param IPoint $pointType The object of the point type to register
     */
    public function registerPointType(IPoint $pointType): void
    {
        $this->pointTypes[] = $pointType;
    }

    /**
     * Returns all registered point types
     * @return IPoint[] All registered point types
     */
    private function getPointTypes(): array
    {
        return $this->pointTypes;
    }

    /**
     * Returns the products in the cart
     * @return CartItem[]
     */
    private function getCartPositions(): array {
        $cart = Frontend::getCart();
        $cartPositions = $cart->PositionenArr;
        return $cartPositions;
    }
}