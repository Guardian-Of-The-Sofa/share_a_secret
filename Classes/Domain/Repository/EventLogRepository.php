<?php

namespace Hn\ShareASecret\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EventLogRepository extends Repository
{
    /** @var QuerySettingsInterface */
    private $querySettings;

    public function save()
    {
        $this->persistenceManager->persistAll();
    }
}