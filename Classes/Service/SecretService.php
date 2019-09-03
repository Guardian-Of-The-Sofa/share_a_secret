<?php

namespace Hn\HnShareSecret\Service;

use DateTime;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use Hn\HnShareSecret\Exceptions\SecretNotFoundException;
use RangeException;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class SecretService
{
    /**
     * @var SecretRepository;
     */
    private $secretRepository;
    private $typo3Key;
    private $userPasswordCharacters = [
        // The letters I, l and O, 0 are removed since they are hard to distinguish on some fonts.
        'letters' => [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M',
            'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        ],

        'digits' => [
            '2', '3', '4', '5', '6', '7', '8', '9',
        ],

        // '{' and '}' are being used to delimit the regular
        // expression in function userPasswordIsValid()
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
     * @param string $message
     * @param string $userPassword
     * @return string, a link hash for the secret
     * @throws EnvironmentIsBrokenException
     * @throws InvalidArgumentValueException
     * @throws IllegalObjectTypeException
     * @throws Exception
     */
    public function createSecret(string $message, string $userPassword): string
    {
        $message = trim($message);
        $userPassword = trim($userPassword);
        if (!($message && $userPassword)) {
            throw new InvalidArgumentValueException();
        }

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
     * @return Secret
     * @throws SecretNotFoundException
     */
    public function getSecret(string $userPassword, $linkHash): Secret
    {
        $indexHash = $this->createIndexHash($userPassword, $linkHash);
        $secret = $this->secretRepository->findOneByIndexHash($indexHash);
        if (!$secret) {
            throw new SecretNotFoundException();
        }
        return $secret;
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
     * @param int $numOfChars , the number of characters to generate.
     * @return string
     * @throws RangeException, if the number of characters to generate is less than 4.
     * @throws Exception
     */
    public function generateUserPassword(int $numOfChars)
    {
        // this depends on the rules in self::userPasswordIsValid
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
     * @param string $userPassword
     * @param string $linkHash
     */
    public function deleteSecret(string $userPassword, string $linkHash)
    {
        try {
            $secret = $this->getSecret($userPassword, $linkHash);
            $this->secretRepository->deleteSecret($secret);
        } catch (SecretNotFoundException $e) {}
    }

    public function deleteSecretByIndexHash(string $indexHash)
    {
        $secret = $this->secretRepository->findOneByIndexHash($indexHash);
        if($secret){
            $this->secretRepository->deleteSecret($secret);
        }
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     */
    private function createPassword(string $userPassword, string $linkHash): string
    {
        return $userPassword . $this->typo3Key . $linkHash;
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     */
    private function createIndexHash(string $userPassword, string $linkHash)
    {
        $password = $this->createPassword($userPassword, $linkHash);
        return hash('sha512', $password);
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
}
