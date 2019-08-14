<?php
namespace Hn\HnShareSecret\Domain\Model;

use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Credential extends AbstractEntity{
    protected $company;
    protected $username;
    protected $passwordHash;

    /**
     * Credential constructor.
     * @param string $company
     * @param string $username
     * @param string $plainPassword
     * @throws InvalidPasswordHashException
     */
    public function __construct(string $company, string $username, string $plainPassword){
        $this->company = $company;
        $this->username = $username;
        $this->passwordHash = $this->generatePasswordHash($plainPassword);
    }

    /**
     * @param string $plainPassword
     * @return string
     * @throws InvalidPasswordHashException
     */
    public function generatePasswordHash(string $plainPassword): string{
        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)
            ->getDefaultHashInstance('FE');

        return $hashInstance->getHashedPassword($plainPassword);
    }
}