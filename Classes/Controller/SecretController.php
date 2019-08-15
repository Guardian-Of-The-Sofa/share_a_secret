<?php

namespace Hn\HnShareSecret\Controller;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Repository\SecretRepository;
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
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

    public function signInAction()
    {

    }

    public function newAction()
    {

    }

    /**
     * @param string $message
     * @param string $plainPassword
     * @throws IllegalObjectTypeException
     * @throws InvalidPasswordHashException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws EnvironmentIsBrokenException
     */
    public function createAction(string $message, string $plainPassword)
    {
        $secret = new Secret($message, $plainPassword);
        $this->secretRepository->add($secret);
        $this->objectManager->get(PersistenceManager::class)->persistAll();

        $this->redirect('showLink', null, null, ['secret' => $secret]);
    }

    /**
     * @param Secret $secret
     */
    public function showLinkAction(Secret $secret)
    {
        $this->view->assign('secret', $secret);
        $host = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
//        $validParameters = [
//            'SCRIPT_NAME',
//            'SCRIPT_FILENAME',
//            'REQUEST_URI',
//            'PATH_INFO',
//            'REMOTE_ADDR',
//            'REMOTE_HOST',
//            'HTTP_REFERER',
//            'HTTP_HOST',
//            'HTTP_USER_AGENT',
//            'HTTP_ACCEPT_LANGUAGE',
//            'QUERY_STRING',
//            'TYPO3_DOCUMENT_ROOT',
//            'TYPO3_HOST_ONLY',
//            'TYPO3_HOST_ONLY',
//            'TYPO3_REQUEST_HOST',
//            'TYPO3_REQUEST_URL',
//            'TYPO3_REQUEST_SCRIPT',
//            'TYPO3_REQUEST_DIR',
//            'TYPO3_SITE_URL',
//            '_ARRAY',
//        ];
//
//        foreach ($validParameters as $vP) {
//
//            debug(GeneralUtility::getIndpEnv($vP),$vP);
//        }
//        die();
        $this->view->assign('host', $host);
    }

    /**
     * @param string $password
     * @param string $linkHash
     * @throws InvalidPasswordHashException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function validatePasswordAction(string $password, string $linkHash)
    {
        /**
         * @var Secret $secret
         */
        //TODO: make sure $linkHash ist distinct.
        $secret = $this->secretRepository->findOneByLinkHash($linkHash);
        if ($secret->validatePassword($password)) {
            $this->forward('show', null, null, [
                'secret' => $secret,
                'password' => $password
            ]);
//            $this->redirect('show', null, null, [
//                'secret' => $secret,
//                'password' => $password,
//            ]);
        } else {
            $this->redirect('inputPassword', null, null, [
                'linkHash' => $linkHash,
            ]);
        }
    }

    public function inputPasswordAction(string $linkHash)
    {
        $this->view->assign('linkHash', $linkHash);
    }

    public function delay(Secret &$secret)
    {
        $diffTime = (new \DateTime())->getTimestamp() - $secret->getLastAttempt();
        self::mylog('$diffTime=' . $diffTime);
        if ($diffTime < 5) {
            sleep(1.5 ** $secret->getAttempt());
        } else {
            $secret->setAttempt(0);
        }
    }

    /**
     * @param string $linkHash
     * @param string $password
     * @throws EnvironmentIsBrokenException
     * @throws IllegalObjectTypeException
     * @throws InvalidPasswordHashException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws WrongKeyOrModifiedCiphertextException
     * @throws UnknownObjectException
     */
    public function showAction(string $linkHash, string $password)
    {
        $secret = $this->secretRepository->findOneByLinkHash($linkHash);

        if ($secret) {
            if (!$secret->validatePassword($password)) {
                $this->delay($secret);
                $attempt = $secret->getAttempt();
                $secret->setAttempt(++$attempt);
                $secret->updateLastAttempt();
                $this->secretRepository->update($secret);
                $this->objectManager->get(PersistenceManager::class)->persistAll();
                $this->redirect('inputPassword', null, null, [
                    'linkHash' => $linkHash,
                ]);
            } else {
                $this->view->assign('message', $secret->getDecryptedMessage($password));
            }
        } else {
            sleep(5);
            $this->redirect('inputPassword', null, null, [
                'linkHash' => $linkHash,
            ]);
        }
    }
}