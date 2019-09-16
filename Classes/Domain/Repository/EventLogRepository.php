<?php

namespace Hn\ShareASecret\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class EventLogRepository extends Repository
{
    public function save()
    {
        $this->persistenceManager->persistAll();
    }
}