<?php

namespace Plugin\dh_bonuspunkte\source\classes\frontend\script;

use Exception;
use JTL\Shop;
use Plugin\dh_bonuspunkte\source\classes\debug\DebugManager;

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
            $consoleMessage = DebugManager::outputMessagesCode();
            pq("head")->append($consoleMessage);
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
        try {
            $scriptFilePath = realpath(__DIR__ . "/../../../../frontend/js/main.js");
            if($scriptFilePath === false) {
                throw new Exception("The webpack script couldn't be loaded, the file doesn't exist.");
            } else {
                $scriptElement = sprintf("<script>%s</script>", file_get_contents($scriptFilePath));
                pq("head")->append($scriptElement);
            }
        } catch (Exception $exception) {
            $this->writePersistentErrorMessage($exception);
        }
    }

    /**
     * Write a persistent error message to the shop log service
     * @noinspection PhpUnhandledExceptionInspection
     */
    private function writePersistentErrorMessage(Exception $exception): void
    {
        Shop::Container()->getLogService()->critical($exception->getMessage(), $exception->getTrace());
    }
}