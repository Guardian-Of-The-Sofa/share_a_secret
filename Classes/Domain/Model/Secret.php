<?php

namespace Hn\HnShareSecret\Domain\Model;

use DateTime;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
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
    protected $passwordHash;

    /**
     * @var string
     */
    protected $linkHash;

    /**
     * @var int, the number of failed password inputs.
     */
    protected $attempt;

    /**
     * @var int, the unix time of the last failed password input.
     */
    protected $lastAttempt;


    /**
     * Secret constructor.
     * @param string $message , A plaintext message.
     * @param string $plainPassword , the plaintext password to encrypt the message.
     * @throws InvalidPasswordHashException
     * @throws EnvironmentIsBrokenException
     */
    public function __construct(string $message, string $plainPassword)
    {
        $this->setMessage($this->encryptMessage($message, $plainPassword));
        $this->setPasswordHash($this->generatePasswordHash($plainPassword));
        $this->setLinkHash($this->generateLinkHash());
        $this->attempt = 0;
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

    /**
     * @return string
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    /**
     * @return string
     */
    public function getLinkHash(): string
    {
        return $this->linkHash;
    }

    /**
     * @param string $linkHash
     */
    public function setLinkHash(string $linkHash): void
    {
        $this->linkHash = $linkHash;
    }

    /**
     * @return int
     */
    public function getAttempt(): ?int
    {
        return $this->attempt;
    }

    /**
     * @param int $attempt
     */
    public function setAttempt(int $attempt): void
    {
        $this->attempt = $attempt;
    }

    /**
     * @return int
     */
    public function getLastAttempt(): ?int
    {
        return $this->lastAttempt;
    }

    /**
     * @param int $lastAttempt
     */
    public function setLastAttempt(int $lastAttempt): void
    {
        $this->lastAttempt = $lastAttempt;
    }

    public function updateLastAttempt()
    {
        $this->lastAttempt = (new DateTime())->getTimestamp();
    }

    /**
     * @param string $plainPassword
     * @return string
     * @throws InvalidPasswordHashException
     */
    public function generatePasswordHash(string $plainPassword): string
    {
        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)
            ->getDefaultHashInstance('FE');

        return $hashInstance->getHashedPassword($plainPassword);
    }

    public function generateLinkHash(): string
    {
        $string = $this->message
            . $this->passwordHash
            . strval((new DateTime())->getTimestamp() * 1.0 / random_int(1, PHP_INT_MAX));

        return hash('sha512', $string);
    }

    /**
     * @param string $plainPassword
     * @return bool
     * @throws InvalidPasswordHashException
     */
    public function validatePassword(string $plainPassword): bool
    {
        return GeneralUtility::makeInstance(PasswordHashFactory::class)
            ->get($this->passwordHash, 'FE')
            ->checkPassword($plainPassword, $this->passwordHash);
    }

    /**
     * @param string $message
     * @param string $plainPassword
     * @return string
     * @throws EnvironmentIsBrokenException
     */
    public function encryptMessage(string $message, string $plainPassword)
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