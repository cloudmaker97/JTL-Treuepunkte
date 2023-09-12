<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use JTL\Plugin\Bootstrapper;
use Plugin\dh_bonuspunkte\source\classes\points\cart\PointsPerArticleOnce;
use Plugin\dh_bonuspunkte\source\classes\points\cart\PointsPerEuro;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\evaluate\PointEvaluator;
use Plugin\dh_bonuspunkte\source\classes\points\cart\PointsPerArticle;

/**
 * Class Bootstrap
 * @package Plugin\dh_bonuspunkte
 */
class Bootstrap extends Bootstrapper
{
    public function boot(\JTL\Events\Dispatcher $dispatcher)
    {
        // Initialize the point evaluator and add the point types
        $evaluator = $this->initializeEvaluator();
        // Reset the debug messages and add some messages
        DebugManager::resetMessages();
        DebugManager::addMessage(new \Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage("dh_bonuspunkte loaded", []));
        DebugManager::addMessage(new \Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage("Evaluated Points: ", [
            "points" => $evaluator->getAllResultPoints()
        ]));
        // Listener after last include: Reload the page after a cart position is added or changed
        $dispatcher->listen('shop.hook.'.HOOK_LETZTERINCLUDE_INC, [$this, 'reloadPageAfterCartChange']);
        // Listener before smarty include: Add the debug messages to the DOM
        $dispatcher->listen('shop.hook.'.HOOK_SMARTY_OUTPUTFILTER, [$this, 'includeDebugMessagesToDOM']);
    }

    /**
     * Add the debug messages to the DOM before the smarty template is rendered,
     * because the PHPQuery DOM is not available anymore after the smarty template is rendered
     */
    private function includeDebugMessagesToDOM(): void {
        $consoleMessage = DebugManager::outputMessagesCode();
        pq("head")->append($consoleMessage);
    }

    /**
     * If a cart position is added or changed, reload the page to update the points 
     * Seems to be a bug in JTL, that the cart positions are not updated in the session immediately
    */
    private function reloadPageAfterCartChange(): void {
        // The attribute "anzahl" is set, if a cart single position is added or changed
        if(isset($_POST["anzahl"])) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            header(sprintf("Location: %s", $currentUrl));
            die;
        }
    }

    /**
     * Initialize the point evaluator and add the point types
     */
    private function initializeEvaluator(): PointEvaluator {
        $evaluator = new PointEvaluator();
        $evaluator->registerPointType(new PointsPerArticle());
        $evaluator->registerPointType(new PointsPerArticleOnce());
        $evaluator->registerPointType(new PointsPerEuro());
        $evaluator->evaluatePoints();
        return $evaluator;
    }
}
