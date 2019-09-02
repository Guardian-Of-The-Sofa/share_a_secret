<?php

namespace Hn\HnShareSecret\Controller;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Exception;
use Hn\HnShareSecret\Exceptions\SecretNotFoundException;
use Hn\HnShareSecret\Service\SecretService;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

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
     * @param \Hn\HnShareSecret\Service\SecretService $secretService
     */
    public function __construct(\Hn\HnShareSecret\Service\SecretService $secretService)
    {
        parent::__construct();
        $this->secretService = $secretService;
    }

    /**
     * @throws Exception
     */
    public function newAction()
    {
        if ($GLOBALS['BE_USER'] === null) {
            $this->redirect('pleaseLogin');
        }
        $userPassword = $this->secretService->generateUserPassword(8);
        if ($this->request->hasArgument('isInvalid')) {
            $isInvalid = $this->request->getArgument('isInvalid');
            $this->view->assign('isInvalid', $isInvalid);
        }
        $this->view->assign('userPassword', $userPassword);
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
        try {
            $linkHash = $this->secretService->createSecret($message, $userPassword);
            $this->redirect('showLink', null, null, [
                'linkHash' => $linkHash,
                'userPassword' => $userPassword
            ]);
        } catch (InvalidArgumentValueException $e) {
            $this->redirect('new', null, null, [
                'isInvalid' => [
                    'message' => strlen(trim($message)) === 0,
                ],
            ]);
        }
    }

    /**
     * @param string $linkHash
     * @param string $userPassword
     */
    public function showLinkAction(string $linkHash, string $userPassword)
    {
        $this->view->assign('linkHash', $linkHash);
        $this->view->assign('userPassword', $userPassword);
    }

    public function inputPasswordAction(string $linkHash, bool $isInvalid = false)
    {
        $this->view->assign('linkHash', $linkHash);
        $this->view->assign('isInvalid', $isInvalid);
    }

    /**
     * @param string $linkHash
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws Exception
     */
    public function redirectToInputPassword(string $linkHash): void
    {
        sleep(3 + random_int(0, 2));
        $this->redirect('inputPassword', null, null, [
            'linkHash' => $linkHash,
            'isInvalid' => true,
        ]);
    }

    /**
     * @param string $linkHash
     * @param string $userPassword
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws EnvironmentIsBrokenException
     */
    public function showAction(string $linkHash, string $userPassword)
    {
        try {
            $secret = $this->secretService->getSecret($userPassword, $linkHash);
            $message = $this->secretService->getDecryptedMessage($secret, $userPassword, $linkHash);
            $this->view->assign('message', $message);
            $this->view->assign('indexHash', $secret->getIndexHash());
        } catch (
        SecretNotFoundException |
        InvalidArgumentValueException |
        WrongKeyOrModifiedCiphertextException $e
        ) {
            $this->redirectToInputPassword($linkHash);
        }
    }

    public function pleaseLoginAction() {}

    public function deleteMessageAction(string $indexHash)
    {
        $this->secretService->deleteSecretByIndexHash($indexHash);
    }
}