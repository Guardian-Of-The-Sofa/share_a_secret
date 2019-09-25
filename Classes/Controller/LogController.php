<?php

namespace Hn\ShareASecret\Controller;

use Hn\ShareASecret\Service\EventLogService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
        $events = $this->eventLogService->findAllDescending();
        $columns = ['date', 'message', 'secret'];
        $this->view->assign('events', $events);
        $this->view->assign('columns', $columns);
    }
}