<?php

namespace Hn\HnShareSecret\Controller;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Service\SecretService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

//TODO: sortiere methoden nach public private....

/**
 * Class SecretController
 */
class SecretController extends ActionController
{
    /**
     * @var SecretService
     */
    private $secretService;

    /**
     * SecretController constructor.
     */
    public function __construct()
    {
        $this->secretService = new SecretService();
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
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws Exception
     */
    public function createAction(string $message, string $userPassword)
    {
        $linkHash = $this->secretService->createSecret($message, $userPassword);
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
     * @throws Exception
     */
    private function delay()
    {
        sleep(3 + random_int(0, 2));
    }

    /**
     * @param string $linkHash
     * @param string $userPassword
     * @throws EnvironmentIsBrokenException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws Exception
     */
    public function showAction(string $linkHash, string $userPassword)
    {
        $password = $this->secretService->createPassword($userPassword, $linkHash);

        $secret = $this->secretService->getSecret($userPassword, $linkHash);//$this->secretRepository->findOneByIndexHash($indexHash);
        if (!$secret) {
            $this->delay();
            $this->redirect('inputPassword', null, null, [
                'linkHash' => $linkHash,
            ]);
        }

        try {
            $message = $secret->getDecryptedMessage($password);
            $this->view->assign('message', $message);
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            $this->delay();
            $this->redirect('inputPassword', null, null, [
                'linkHash' => $linkHash,
            ]);
        }
    }
}