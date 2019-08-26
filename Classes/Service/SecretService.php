<?php

namespace Hn\HnShareSecret\Service;

use DateTime;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use RangeException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;

class SecretService
{
    /**
     * @var SecretRepository;
     */
    private $secretRepository;
    private $typo3Key;
    private $userPasswordCharacters = [
        'letters' => [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        ],

        'digits' => [
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
        ],

        'specialCharacters' => [
            '!', '#', '$', '%', '&', '(', ')', '*', '+', ',',
            '-', '.', '/', ':', ';', '=', '?', '@', '\\', '_', '~',
        ],
    ];

    private $userPasswordChars;

    /**
     * SecretService constructor.
     * @param \Hn\HnShareSecret\Domain\Repository\SecretRepository $secretRepository
     */
    public function __construct(\Hn\HnShareSecret\Domain\Repository\SecretRepository $secretRepository)
    {
        $this->typo3Key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $this->secretRepository = $secretRepository;
        $this->userPasswordChars = array_merge(
            $this->userPasswordCharacters['letters'],
            $this->userPasswordCharacters['digits'],
            $this->userPasswordCharacters['specialCharacters']
        );
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     * @throws InvalidArgumentValueException
     */
    public function createPassword(string $userPassword, string $linkHash): string
    {
        if (!($userPassword && $linkHash)) {
            throw new InvalidArgumentValueException();
        }
        return $userPassword . $this->typo3Key . $linkHash;
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     * @throws InvalidArgumentValueException
     */
    public function createIndexHash(string $userPassword, string $linkHash)
    {
        $password = $this->createPassword($userPassword, $linkHash);
        return hash('sha512', $password);
    }

    /**
     * @param $message
     * @param $userPassword
     * @return string
     * @throws Exception
     */
    public function createSecret(string $message, string $userPassword): string
    {
        $linkHash = $this->generateLinkHash();
        $password = $this->createPassword($userPassword, $linkHash);
        $indexHash = $this->createIndexHash($userPassword, $linkHash);
        $encMessage = $this->encryptMessage($message, $password);
        $secret = new Secret($encMessage, $indexHash);
        $this->secretRepository->add($secret);
        $this->secretRepository->save();
        return $linkHash;
    }

    /**
     * @param string $userPassword
     * @param $linkHash
     * @return Secret|null
     */
    public function getSecret(string $userPassword, $linkHash): ?Secret
    {
        $indexHash = $this->createIndexHash($userPassword, $linkHash);
        $secret = $this->secretRepository->findOneByIndexHash($indexHash);
        return $secret;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function generateLinkHash(): string
    {
        $string = strval((new DateTime())->getTimestamp() * 1.0 / random_int(1, PHP_INT_MAX));

        return hash('sha512', $string);
    }

    /**
     * @param Secret $secret
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function getDecryptedMessage(Secret $secret, string $userPassword, string $linkHash): string
    {
        $password = $this->createPassword($userPassword, $linkHash);
        $decryptedMessage = Crypto::decryptWithPassword($secret->getMessage(), $password);
        return $decryptedMessage;
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

    public function userPasswordIsValid(string $userPassword)
    {
        $specialChars = implode($this->userPasswordCharacters['specialCharacters']);
        $isValid = false;
        if (
            preg_match('/[A-Z]/', $userPassword) &&
            preg_match('/[a-z]/', $userPassword) &&
            preg_match('/[0-9]/', $userPassword) &&
            preg_match("{[$specialChars]}", $userPassword)
        ) {
            $isValid = true;
        }
        return $isValid;
    }

    /**
     * @param int $numOfChars , the number of characters to generate.
     * @return string
     * @throws Exception
     */
    public function generateUserPassword(int $numOfChars)
    {
        //TODO: Exception werfen oder $numOfChars auf Standardwert setzen?
        if ($numOfChars < 4) {
            throw new RangeException('$numOfChars must be at least 4, ' . $numOfChars . ' given.');
        }

        $maxIndex = count($this->userPasswordChars) - 1;
        $userPassword = '';
        while (!$this->userPasswordIsValid($userPassword)) {
            $userPassword = ''; // Reset non-valid password
            for ($i = 0; $i < $numOfChars; $i++) {
                $userPassword .= $this->userPasswordChars[random_int(0, $maxIndex)];
            }
        }
        return $userPassword;
    }
}