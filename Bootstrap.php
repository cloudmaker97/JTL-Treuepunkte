<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use Exception;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use Plugin\dh_bonuspunkte\source\classes\frontend\PageController;
use Plugin\dh_bonuspunkte\source\classes\frontend\script\ScriptManager;
use Plugin\dh_bonuspunkte\source\classes\frontend\script\ScriptType;
use Plugin\dh_bonuspunkte\source\classes\points\CartAbstractPoints;
use Plugin\dh_bonuspunkte\source\classes\points\LoginAbstractPoints;
use Plugin\dh_bonuspunkte\source\classes\points\RegisterAbstractPoints;
use Plugin\dh_bonuspunkte\source\classes\points\VisitAbstractPoints;
use Plugin\dh_bonuspunkte\source\classes\rewards\products\ProductRewards;

/**
 * Class Bootstrap
 * @package Plugin\dh_bonuspunkte
 */
class Bootstrap extends Bootstrapper
{
    private Dispatcher $dispatcher;
    private ScriptManager $scriptManager;

    /**
     * @inheritDoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        global $pluginInterfaceForDhBonuspoints;
        $pluginInterfaceForDhBonuspoints = $this->getPlugin();
        $this->scriptManager = new ScriptManager();
        $this->dispatcher = $dispatcher;
        $this->dispatcherListeners();
    }

    /**
     * The script manager is used to simplify the injection of certain
     * scripts and snippets. Just use it via the `loadScript` method.
     * @return ScriptManager
     */
    public function getScriptManager(): ScriptManager
    {
        return $this->scriptManager;
    }

    /**
     * Register the event listeners for the plugin,
     * so that the plugin can react to the events of the shop
     */
    private function dispatcherListeners(): void
    {
        // Hook: Update data in articles after loaded
        $this->dispatcher->listen('shop.hook.'.HOOK_ARTIKEL_CLASS_FUELLEARTIKEL, function ($args) {
            (new ProductRewards())->updateProductAfterLoaded($args['oArtikel']);
        });
        // Hook: Page in the frontend is loaded
        $this->dispatcher->listen('shop.hook.' . HOOK_SEITE_PAGE, function () {
            new PageController();
        });
        // Hook: Order status change
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, function ($args) {
            (new CartAbstractPoints($args))->setOrderStatusProcessed();
        });
        // Hook: Order status changed to canceled
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, function ($args) {
            (new CartAbstractPoints($args))->setOrderStatusCanceled();
        });
        // Hook: Cart finalization
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, function ($args) {
            (new CartAbstractPoints($args))->executeRewardLogic();
        });
        // Hook: Account Login
        $this->dispatcher->listen('shop.hook.' . HOOK_KUNDE_CLASS_HOLLOGINKUNDE, function ($args) {
            (new LoginAbstractPoints($args))->executeRewardLogic();
        });
        // Hook: Registration
        $this->dispatcher->listen('shop.hook.' . HOOK_REGISTRATION_CUSTOMER_CREATED, function ($args) {
            (new RegisterAbstractPoints($args))->executeRewardLogic();
        });
        // Hook: Each visit, before the smarty template is rendered
        $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function ($args) {
            try {
                (new ProductRewards())->reloadPageAfterCartChange();
                (new VisitAbstractPoints($args))->executeRewardLogic();
                $this->getScriptManager()->loadScript(ScriptType::DebugMessages);
                $this->getScriptManager()->loadScript(ScriptType::WebpackInline);
            } catch (Exception) {
                // If an exception is thrown, the page will be served without the bonus points
                // Just in case something goes wrong, the user should still be able to use the shop
            }
        });

        (new ProductRewards())->updateCartPositionsForRewardProducts();
    }
}