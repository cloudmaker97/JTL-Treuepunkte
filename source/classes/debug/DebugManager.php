<?php
namespace Plugin\dh_bonuspunkte\source\classes\debug;
use Plugin\dh_bonuspunkte\source\interfaces\debug\IDebugMessage;

/**
 * Class DebugManager
 * Handles all debug messages, from adding, resetting and outputting them
 */
class DebugManager {
    /**
     * @var string The session key name for the debug messages
     */
    private const SESSION_KEY_NAME = "dh_bonuspunkte_debug_messages";

    /**
     * @return string Returns all debug messages as JS-Console HTML code
     */
    public static function outputMessagesCode(): string
    {
        $logMessage = [];
        foreach(static::getMessages() as $messages) {
            $logMessage[] = $messages->getConsoleCode();
        }
        $allMessages = implode("\n", $logMessage);
        return sprintf("<script>%s</script>", $allMessages);
    }

    /**
     * Reset all debug messages
     */
    public static function resetMessages(): void
    {
        $_SESSION[self::SESSION_KEY_NAME] = [];
    }

    /**
     * Adds a debug message to the session
     * @param IDebugMessage $debugMessage The debug message to add
     */
    public static function addMessage(IDebugMessage $debugMessage): void
    {
        if($_SESSION[self::SESSION_KEY_NAME] == null) {
            $_SESSION[self::SESSION_KEY_NAME] = [];
        }

        // Add the debug message to the session, it's type safe
        $_SESSION[self::SESSION_KEY_NAME][] = $debugMessage;
    }

    /**
     * @return IDebugMessage[] Returns all debug messages
     */
    public static function getMessages(): array
    {
        if($_SESSION[self::SESSION_KEY_NAME] == null) {
            $_SESSION[self::SESSION_KEY_NAME] = [];
        }

        $debugMessages = [];
        foreach($_SESSION[self::SESSION_KEY_NAME] as $debugMessage) {
            // Only add debug messages implementing the IDebugMessage interface
            if($debugMessage instanceof IDebugMessage) {
                $debugMessages[] = $debugMessage;
            }
        }
        
        return $debugMessages;
    }
}