<?php

namespace Hn\ShareASecret\Controller;

use Hn\ShareASecret\Service\ActivityChartService;
use Hn\ShareASecret\Service\EventLogService;
use Hn\ShareASecret\Service\StatisticService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class LogController extends ActionController
{
    /* @var StatisticService */
    private $statisticService;

    /* @var ActivityChartService */
    private $activityChartService;

    public function __construct(
        StatisticService $statisticService,
        ActivityChartService $activityChartService
    )
    {
        $this->statisticService = $statisticService;
        $this->activityChartService = $activityChartService;
    }

    public function listAction()
    {
        $statistics = $this->statisticService->getStatistics();
        $this->view->assign('statistics', $statistics);
        $this->view->assign('activityChartConfig', $this->activityChartService->getActivityChartConfig());
    }
}