<?php

namespace Hn\HnShareSecret\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;

class Secret extends AbstractEntity
{
    /**
     * @var string, an encrypted message.
     */
    protected $message;

    /**
     * @var string
     */
    protected $indexHash;

    /**
     * Secret constructor.
     * @param string $encMessage
     * @param $indexHash
     */
    public function __construct(string $encMessage, $indexHash)
    {
        if (! ($encMessage && $indexHash)){
            throw new InvalidArgumentValueException();
        }

        $this->message = $encMessage;
        $this->indexHash = $indexHash;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getIndexHash(): string
    {
        return $this->indexHash;
    }
}