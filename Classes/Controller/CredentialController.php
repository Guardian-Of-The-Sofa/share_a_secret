<?php

namespace Hn\HnShareSecret\Controller;

use Hn\HnShareSecret\Domain\Repository\CredentialRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;

class CredentialController extends ActionController
{
    /**
     * @var CredentialRepository
     */
    private $credentialRepository;

    /**
     * @param CredentialRepository $credentialRepository
     */
    public function injectRepository(CredentialRepository $credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
    }


    /**
     * @param array $arguments
     */
    public function signInAction()
    {

    }

    /**
     * @param string $company
     * @param string $username
     * @param string $plainPassword
     * @param string $plainPasswordReentered
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     */
    public function createAction(string $company,
                                 string $username,
                                 string $plainPassword,
                                 string $plainPasswordReentered)
    {
        if ($plainPassword != $plainPasswordReentered) {
            $this->redirect('signIn', null, null, [
                'arguments' => [
                    'company' => $company,
                    'username' => $username,
                    'plainPassword' => $plainPassword,
                ]
            ]);
        }
    }
}