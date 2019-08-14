<?php

namespace Hn\HnShareSecret\Controller;

use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class UserController extends ActionController
{

    /**
     * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository
     */
    protected $frontendUserRepository;

    public function injectFrontendUserRepository(FrontendUserRepository $frontendUserRepository)
    {
        $this->frontendUserRepository = $frontendUserRepository;
    }

    /**
     * @param string|null $company
     */
    public function newAction(string $company = null)
    {
        debug($frontendUser, 'newAction');
        if($frontendUser){
            $this->view->assign('frontendUser', $frontendUser);
        }
    }

    /**
     * @param FrontendUser $frontendUser
     * @param string $passwordReentered
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws IllegalObjectTypeException
     */
    public function createAction(FrontendUser $frontendUser, string $passwordReentered)
    {
        debug($frontendUser);
        if($frontendUser->getPassword() != $passwordReentered){
            $this->redirect('new', null, null, ['company' => $frontendUser->getCompany()]);
        }

        $this->frontendUserRepository->add($frontendUser);
    }
}