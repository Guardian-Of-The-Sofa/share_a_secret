<?php

namespace Hn\HnShareSecret\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class StatisticRepository extends Repository
{
    public function save()
    {
        $this->persistenceManager->persistAll();
    }
}