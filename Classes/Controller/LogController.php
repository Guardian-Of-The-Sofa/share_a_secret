<?php

namespace Hn\ShareASecret\Controller;

use Hn\ShareASecret\Service\EventLogService;
use Hn\ShareASecret\Service\StatisticService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class LogController extends ActionController
{
    /* @var StatisticService */
    private $statisticService;

    public function __construct(
        StatisticService $statisticService
    )
    {
        parent::__construct();
        $this->statisticService = $statisticService;
    }

    public function listAction()
    {
        $statistics = $this->statisticService->getStatistics();
        $this->view->assign('statistics', $statistics);
    }
}