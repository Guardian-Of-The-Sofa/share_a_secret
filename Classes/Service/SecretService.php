<?php

namespace Hn\HnShareSecret\Service;

use DateTime;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class SecretService
{
    /**
     * @var SecretRepository;
     */
    private $secretRepository;
    private $objectManager;
    private $typo3Key;

//    /**
//     * @param \Hn\HnShareSecret\Domain\Repository\SecretRepository $secretRepository
//     */
//    public function injectRepository(SecretRepository $secretRepository)
//    {
//        $this->secretRepository = $secretRepository;
//    }

    /**
     * SecretService constructor.
     */
    public function __construct()
    {
        $this->typo3Key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        //TODO: Frag Florian warum GeneralUtility... nötig ist.
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->secretRepository = $this->objectManager->get(SecretRepository::class);
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
     */
    public function createPassword(string $userPassword, string $linkHash): string
    {
        return $userPassword . $this->typo3Key . $linkHash;
    }

    /**
     * @param string $userPassword
     * @param string $linkHash
     * @return string
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
        $secret = new Secret($message, $password);
        $indexHash = $this->createIndexHash($userPassword, $linkHash);
        $secret->setIndexHash($indexHash);
        $this->secretRepository->add($secret);
        $this->objectManager->get(PersistenceManager::class)->persistAll();
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
}