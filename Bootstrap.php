<?php declare(strict_types=1);
/**
 * @package Plugin\dh_bonuspunkte
 * @author Dennis Heinrich
 */

namespace Plugin\dh_bonuspunkte;

use Exception;
use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Session\Frontend;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\frontend\PageController;
use Plugin\dh_bonuspunkte\source\classes\frontend\script\ScriptManager;
use Plugin\dh_bonuspunkte\source\classes\frontend\script\ScriptType;
use Plugin\dh_bonuspunkte\source\classes\rewards\CartReward;
use Plugin\dh_bonuspunkte\source\classes\rewards\LoginReward;
use Plugin\dh_bonuspunkte\source\classes\rewards\RegisterReward;
use Plugin\dh_bonuspunkte\source\classes\rewards\VisitReward;

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
        // Hook: Page in the frontend is loaded
        $this->dispatcher->listen('shop.hook.' . HOOK_SEITE_PAGE, function () {
            new PageController();
            
            // Inject the webpack script inline to the head of the page
            $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function () {
                $this->getScriptManager()->loadScript(ScriptType::WebpackInline);
            });
        });
        // Hook: Order status change
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BESTELLSTATUS, function ($args) {
            (new CartReward($args))->setOrderStatusProcessed();
        });
        // Hook: Order status changed to canceled
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLUNGEN_XML_BEARBEITESTORNO, function ($args) {
            (new CartReward($args))->setOrderStatusCanceled();
        });
        // Hook: Cart finalization
        $this->dispatcher->listen('shop.hook.' . HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_ENDE, function ($args) {
            (new CartReward($args))->executeRewardLogic();
        });
        // Hook: Account Login
        $this->dispatcher->listen('shop.hook.' . HOOK_KUNDE_CLASS_HOLLOGINKUNDE, function ($args) {
            (new LoginReward($args))->executeRewardLogic();
        });
        // Hook: Registration
        $this->dispatcher->listen('shop.hook.' . HOOK_REGISTRATION_CUSTOMER_CREATED, function ($args) {
            (new RegisterReward($args))->executeRewardLogic();
        });
        // Hook: Each visit, before the smarty template is rendered
        $this->dispatcher->listen('shop.hook.' . HOOK_SMARTY_OUTPUTFILTER, function ($args) {
            (new VisitReward($args))->executeRewardLogic();
            $this->getScriptManager()->loadScript(ScriptType::DebugMessages);
        });
    }
}