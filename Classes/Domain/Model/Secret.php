<?php

namespace Hn\HnShareSecret\Domain\Model;

use DateTime;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use Defuse\Crypto\Crypto;

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