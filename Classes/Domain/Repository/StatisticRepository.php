<?php

namespace Hn\HnShareSecret\Domain\Repository;

use Hn\HnShareSecret\Domain\Model\Secret;
use Hn\HnShareSecret\Domain\Model\Statistic;
use TYPO3\CMS\Extbase\Persistence\Repository;

class StatisticRepository extends Repository
{
    public function save()
    {
        $this->persistenceManager->persistAll();
    }

    /**
     * @param Secret $secret
     * @return Statistic
     */
    public function findBySecret(Secret $secret): ?Statistic
    {
        $query = $this->createQuery();
        $query->matching($query->equals('secret', $secret));
        return $query->execute()->getFirst() ?: null;
    }
}