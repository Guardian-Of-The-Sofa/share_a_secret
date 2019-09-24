<?php

namespace Hn\ShareASecret\Domain\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class EventLogRepository extends Repository
{
    /** @var QuerySettingsInterface */
    private $querySettings;

    public function initializeObject() {
        $this->querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
    }

    public function save()
    {
        $this->persistenceManager->persistAll();
    }

    public function findAllDescending()
    {
        $this->querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($this->querySettings);
        $query = $this->createQuery()
            ->setOrderings(['uid' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING]);
        $res = $query->execute();
        return $res;
    }
}