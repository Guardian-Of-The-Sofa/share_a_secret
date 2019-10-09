<?php

namespace Hn\ShareASecret\Controller;

use Hn\ShareASecret\Service\EventLogService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Hn\ShareASecret\Utility\ArrayModifier;

class LogController extends ActionController
{
    private $eventLogService;

    public function __construct(EventLogService $eventLogService)
    {
        parent::__construct();
        $this->eventLogService = $eventLogService;
    }

    public function listAction()
    {
        $statistics = $this->eventLogService->getStatistics();
        $this->view->assign('statistics', $statistics);
    }
}