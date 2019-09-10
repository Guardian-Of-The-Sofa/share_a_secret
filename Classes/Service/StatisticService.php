<?php

namespace Hn\HnShareSecret\Service;

use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Model\Statistic;
use Hn\HnShareSecret\Domain\Repository\StatisticRepository;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

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

    /**
     * @param Secret $secret
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function setDeleted(Secret $secret)
    {
        $statistic = $this->statisticRepository->findBySecret($secret);
        $statistic->setDeleted((new \DateTime())->getTimestamp());
        $this->update($statistic);
        $this->statisticRepository->save();
    }

    /**
     * @param Statistic $statistic
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function update(Statistic $statistic)
    {
        $this->statisticRepository->update($statistic);
    }
}