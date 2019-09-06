<?php

namespace Hn\HnShareSecret\Service;

use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Model\Statistic;
use Hn\HnShareSecret\Domain\Repository\StatisticRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\AbstractController;

class StatisticService
{
    /* @var StatisticRepository */
    private $statisticRepository;

    public function __construct(StatisticRepository $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    public function create(Secret $secret, int $read = null, int $deleted = null){
        $statistic = new Statistic();
        $statistic->setSecret($secret);
        $this->statisticRepository->add($statistic);
        $this->statisticRepository->save();
    }

    /**
     * @param Secret $secret
     * @return Statistic
     */
    public function getStatistic(Secret $secret): Statistic
    {
        $secretId = $secret->getUid();
        return $this->statisticRepository->findBySecret($secret);
    }

    public function update(Statistic $statistic)
    {
        $this->statisticRepository->update($statistic);
    }
}