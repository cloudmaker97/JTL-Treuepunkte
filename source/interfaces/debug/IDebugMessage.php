<?php
namespace Plugin\dh_bonuspunkte\source\interfaces\debug;

interface IDebugMessage {
    /**
     * Returns the debug message
     * @return string The debug message
     */
    public function getMessage(): string;

    /**
     * Returns the additional (optional) debug data.
     * If no data is available, an empty array is returned.
     * @return array The additional debug data or an empty array
     */
    public function getData(): array;

    /**
     * Returns the console code for the debug message
     * for the browser console (javascript)
     * @return string The javascript console code
     */
    public function getConsoleCode(): string;
}