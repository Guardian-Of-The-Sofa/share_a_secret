<?php

namespace Hn\HnShareSecret\Controller;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Class SecretController
 */
class SecretController extends ActionController
{

    /**
     * @var SecretRepository
     */
    private $secretRepository;

    public static function mylog(string $str)
    {
        $fH = fopen('mylog.txt', 'a');
        fwrite($fH, $str . "\n");
        fclose($fH);
    }

    /**
     * @param \Hn\HnShareSecret\Domain\Repository\SecretRepository $secretRepository
     */
    public function injectRepository(SecretRepository $secretRepository)
    {
        $this->secretRepository = $secretRepository;
    }

    public function indexAction()
    {

    }

    public function newAction()
    {

    }

    /**
     * @param string $message
     * @param string $userPassword
     * @throws EnvironmentIsBrokenException
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws \Exception
     */
    public function createAction(string $message, string $userPassword)
    {
        $typo3Key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $linkHash = Secret::generateLinkHash();
        $password = $this->makePassword($userPassword, $typo3Key, $linkHash);
        $indexHash = $this->makeIndexHash($userPassword, $typo3Key, $linkHash);
        $secret = new Secret($message, $password);
        $secret->setIndexHash($indexHash);
        $this->secretRepository->add($secret);
        $this->objectManager->get(PersistenceManager::class)->persistAll();

        $this->redirect('showLink', null, null, ['linkHash' => $linkHash]);
    }

    /**
     * @param string $linkHash
     */
    public function showLinkAction(string $linkHash)
    {
        $this->view->assign('linkHash', $linkHash);
    }

    public function inputPasswordAction(string $linkHash)
    {
        $this->view->assign('linkHash', $linkHash);
    }

    /**
     * @throws \Exception
     */
    public function delay()
    {
        sleep(3 + random_int(0, 2));
    }

    /**
     * @param string $userPassword
     * @param string $typo3Key
     * @param string $linkHash
     * @return string
     */
    public function makePassword(string $userPassword, string $typo3Key, string $linkHash): string
    {
        return $userPassword . $typo3Key . $linkHash;
    }

    /**
     * @param string $userPassword
     * @param string $typo3Key
     * @param string $linkHash
     * @return string|\TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface
     * @throws InvalidPasswordHashException
     */
    public function makeIndexHash(string $userPassword, string $typo3Key, string $linkHash)
    {
        $password = $this->makePassword($userPassword, $typo3Key, $linkHash);
        return hash('sha512', $password);
    }

    /**
     * @param string $linkHash
     * @param string $userPassword
     * @throws EnvironmentIsBrokenException
     * @throws IllegalObjectTypeException
     * @throws InvalidPasswordHashException
     * @throws StopActionException
     * @throws UnknownObjectException
     * @throws UnsupportedRequestTypeException
     * @throws \Exception
     */
    public function showAction(string $linkHash, string $userPassword)
    {
        $this->request->getArgument('linkHash');
        $typo3Key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'];
        $indexHash = $this->makeIndexHash($userPassword, $typo3Key, $linkHash);
        $password = $this->makePassword($userPassword, $typo3Key, $linkHash);

        $secret = $this->secretRepository->findOneByIndexHash($indexHash);
        if ($secret) {
            try {
                $message = $secret->getDecryptedMessage($password);
                $this->view->assign('message', $message);
                $this->view->render();
            } catch (WrongKeyOrModifiedCiphertextException $e) {
                $this->delay();
                $this->redirect('inputPassword', null, null, [
                    'linkHash' => $linkHash,
                ]);
            }
        } else {
            $this->delay();
            $this->redirect('inputPassword', null, null, [
                'linkHash' => $linkHash,
            ]);
        }
    }
}