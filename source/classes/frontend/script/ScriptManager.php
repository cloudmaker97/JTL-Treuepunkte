<?php

namespace Plugin\dh_bonuspunkte\source\classes\frontend\script;

use Exception;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugMessage;
use Plugin\dh_bonuspunkte\source\classes\helper\PluginInterfaceAccessor;

/**
 * The script manager is used to load javascript code into the page.
 * It has a method for selecting a script via a ScriptType enum.
 * The scripts are injected via. the shop function "pq" which stands for
 * phpQuery. It is a library for placing and modifying the data object model
 * before it will be rendered by smarty. Due to this call the scripts can only be
 * injected after the Smarty object is instantiated and before it is rendered by the shop, so
 * you can use the event dispatcher hook "HOOK_SMARTY_OUTPUTFILTER"
 */
class ScriptManager
{
    /**
     * Load a single script by calling its ScriptType enum
     * @param ScriptType $scriptType
     * @return void
     */
    public function loadScript(ScriptType $scriptType): void
    {
        match ($scriptType) {
            ScriptType::DebugMessages => $this->injectDebugMessages(),
            ScriptType::WebpackInline => $this->injectWebpackScriptInline(),
        };
    }

    /**
     * Inject the debug messages to the shop head, so you can read it
     * in the javascript console in the browser of the page.
     * @return void
     */
    private function injectDebugMessages(): void
    {
        try {
            $this->appendToHead(DebugManager::outputMessagesCode());
        } catch (Exception $exception) {
            $this->writePersistentErrorMessage($exception);
        } finally {
            DebugManager::resetMessages();
        }
    }

    /**
     * Inject the compiled webpack script at the frontend path.
     * It is used for interactive elements and stylesheets. It is not necessary to load this script
     * via. the plugin script node, it is only used on exclusive pages and would otherwise affect the
     * page speed (Web-Core-Vitals) in a negative way for no reason.
     * @return void
     */
    private function injectWebpackScriptInline(): void
    {
        $pluginInterface = PluginInterfaceAccessor::getPluginInterface();
        $pluginBasePath = $pluginInterface->getPaths()->getBasePath();
        $scriptRealPath = realpath(sprintf("%s/frontend/js/main.js", $pluginBasePath));
        try {
            if($scriptRealPath === false) {
                throw new Exception("The webpack script couldn't be loaded, the file doesn't exist.");
            } else {
                $this->appendToHead(sprintf("<script>%s</script>", file_get_contents($scriptRealPath)));
            }
        } catch (Exception $exception) {
            $this->writePersistentErrorMessage($exception);
        }
    }

    /**
     * Write a persistent error message to the shop log service
     */
    private function writePersistentErrorMessage(Exception $exception): void
    {
        DebugManager::addMessage(new DebugMessage($exception->getMessage()));
        try {
            Shop::Container()->getLogService()->critical($exception->getMessage(), $exception->getTrace());
        } catch (CircularReferenceException|ServiceNotFoundException) {}
    }

    /**
     * Append a string to the header of the current document
     * @throws Exception
     */
    private function appendToHead(string $element): void
    {
        pq("head")->append($element);
    }
}