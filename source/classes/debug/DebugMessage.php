<?php
namespace Plugin\dh_bonuspunkte\source\classes\debug;
use Plugin\dh_bonuspunkte\source\interfaces\debug\IDebugMessage;

/**
 * Class DebugMessage
 * Represents a single debug message
 */
class DebugMessage implements IDebugMessage {
    private string $message;
    private array $data = [];

    /**
     * @inheritDoc
     */
    public function __construct(string $message, array $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }

	/**
     * @inheritDoc
	 */
	public function getMessage(): string {
        return $this->message;
	}

	/**
     * @inheritDoc
	 */
	public function getData(): array {
        return $this->data;
	}

    /**
     * @inheritDoc
	 */
    public function getConsoleCode(): string
    {
        $data = json_encode($this->getData());
        return sprintf("console.log('%s', %s);", $this->getMessage(), $data);
    }
}