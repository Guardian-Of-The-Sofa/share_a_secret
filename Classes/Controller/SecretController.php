<?php

namespace Hn\HnShareSecret\Controller;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Service\SecretService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

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
        //TODO: unschön?
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
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function redirectToInputPassword(string $linkHash): void
    {
        sleep(3 + random_int(0, 2));
        $this->redirect('inputPassword', null, null, [
            'linkHash' => $linkHash,
        ]);
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
        $secret = $this->secretService->getSecret($userPassword, $linkHash);
        if (!$secret) {
            $this->redirectToInputPassword($linkHash);
        }

        try {
            $message = $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash);
            $this->view->assign('message', $message);
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            $this->redirectToInputPassword($linkHash);
        }
    }
}