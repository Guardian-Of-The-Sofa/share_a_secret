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
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $indexHash;

    /**
     * Secret constructor.
     * @param string $message , A plaintext message.
     * @param string $plainPassword , the plaintext password to encrypt the message.
     * @throws EnvironmentIsBrokenException
     * TODO: Refactor machen, dann nächstes Todo.
     * TODO: linkHash im Konstruktor hinzufügen.
     */
    public function __construct(string $message, $plainPassword)
    {
        $this->message = $this->encryptMessage($message, $plainPassword);
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

    /**
     * @param string $indexHash
     */
    public function setIndexHash(string $indexHash): void
    {
        $this->indexHash = $indexHash;
    }

    /**
     * @return string
     * @throws Exception
     */
    public static function generateLinkHash(): string
    {
        $string = strval((new DateTime())->getTimestamp() * 1.0 / random_int(1, PHP_INT_MAX));

        return hash('sha512', $string);
    }

    /**
     * @param string $plainPassword
     * @return bool
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function validatePassword(string $plainPassword): bool
    {
        return Crypto::decryptWithPassword($this->message, $plainPassword);
    }

    /**
     * @param string $message
     * @param string $plainPassword
     * @return string
     * @throws EnvironmentIsBrokenException
     */
    private function encryptMessage(string $message, string $plainPassword)
    {
        $encryptedMessage = Crypto::encryptWithPassword($message, $plainPassword);

        return $encryptedMessage;
    }

    /**
     * @param string $password
     * @return string
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function getDecryptedMessage(string $password)
    {
        $decryptedMessage = Crypto::decryptWithPassword($this->message, $password);

        return $decryptedMessage;
    }
}