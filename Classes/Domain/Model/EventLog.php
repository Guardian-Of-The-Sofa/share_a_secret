<?php

namespace Hn\ShareASecret\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class EventLog extends AbstractEntity
{
    const CREATE = 0;
    const DELETE = 1;
    const REQUEST = 2;
    const SUCCESS = 3;
    const NOTFOUND = 4;

    /**
     * @var Secret
     */
    protected $secret;

    /**
     * @var int
     */
    protected $date;

    /**
     * @var int
     */
    protected $event;

    /**
     * @var string
     */
    protected $message;

    public function __construct(int $event, Secret $secret = null)
    {
        $this->secret = $secret;
        $this->event = $event;
        $this->setMessageByEvent($event);
    }

    public function setMessageByEvent(int $event)
    {
        switch ($event){
            case self::CREATE:
                $this->message = "Secret was created";
                break;
            case self::SUCCESS:
                $this->message = "Password attempt succeeded";
                break;
            case self::DELETE:
                $this->message = "Secret was deleted";
                break;
            case self::REQUEST:
                $this->message = "Secret was requested";
                break;
            case self::NOTFOUND:
                $this->message = "Attempt to access a non existing Secret";
                break;
        }
    }

    /**
     * @return int
     */
    public function getDate(): int
    {
        return $this->date;
    }

    /**
     * @param int $date
     */
    public function setDate(int $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}