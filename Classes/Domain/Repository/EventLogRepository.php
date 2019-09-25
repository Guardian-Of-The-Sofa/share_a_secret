<?php

namespace Hn\ShareASecret\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    public function findAllDescending()
    {
        $query = $this->createQuery()
            ->setOrderings(['uid' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $query->getQuerySettings()->setRespectStoragePage(false);
        $res = $query->execute();
        return $res;
    }
}