<?php


namespace Hn\ShareASecret\Service;

use Hn\ShareASecret\Domain\Model\EventLog;
use Hn\ShareASecret\Domain\Model\Secret;
use Hn\ShareASecret\Domain\Repository\EventLogRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class EventLogService
{
    /**
     * @var EventLogRepository
     */
    private $eventLogRepository;

    /**
     * @var QueryBuilder $preparedQueryBuilder
     */
    private $preparedQueryBuilder;

    public function __construct(EventLogRepository $eventLogRepository)
    {
        $this->eventLogRepository = $eventLogRepository;
        $this->initPreparedQueryBuilder();
    }

    public function log(EventLog $eventLog)
    {
        $this->eventLogRepository->add($eventLog);
        $this->eventLogRepository->save();
    }

    public function logCreate(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::CREATE, $secret));
    }

    public function logSuccess(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::SUCCESS, $secret));
    }

    public function logDelete(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::DELETE, $secret));
    }

    public function logRequest(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::REQUEST, $secret));
    }

    public function logNotFound(Secret $secret = null)
    {
        $this->log(new EventLog(EventLog::NOTFOUND, $secret));
    }

    private function initPreparedQueryBuilder()
    {
        $this->preparedQueryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_shareasecret_domain_model_eventlog');

        $this->preparedQueryBuilder
            ->select(
                'el.date AS creationDate',
                'el.secret',
                'el.event AS creationEvent',
                'er.date AS logDate',
                'er.event'
            )
            ->from('tx_shareasecret_domain_model_eventlog', 'el')
            ->innerJoin(
                'el',
                'tx_shareasecret_domain_model_eventlog',
                'er',
                $this->preparedQueryBuilder->expr()->eq(
                    'el.secret',
                    $this->preparedQueryBuilder->quoteIdentifier('er.secret')
                )
            )
            ->where(
                $this->preparedQueryBuilder->expr()->eq(
                    'el.event',
                    $this->preparedQueryBuilder->createNamedParameter(EventLog::CREATE)
                )
            );
    }

    public function getPreparedQueryBuilder()
    {
        return $this->preparedQueryBuilder;
    }

    public function getCreatedSecrets()
    {
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = clone $this->getPreparedQueryBuilder();

        /**
         * get all created secrets
         */
        $statement = $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'er.event',
                    $queryBuilder->createNamedParameter(EventLog::CREATE)
                )
            )
            ->groupBy('secret')
            ->execute();
        return $statement->fetchAll();
    }

    public function getReadSecrets()
    {
        $queryBuilder = clone $this->getPreparedQueryBuilder();
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select(
                'el.date AS creationDate',
                'el.secret',
                'el.event AS creationEvent',
                'er.date AS logDate',
                'er.event'
            )
            ->from('tx_shareasecret_domain_model_eventlog', 'el')
            ->innerJoin(
                'el',
                'tx_shareasecret_domain_model_eventlog',
                'er',
                $queryBuilder->expr()->eq(
                    'el.secret',
                    $queryBuilder->quoteIdentifier('er.secret')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'el.event',
                    $queryBuilder->createNamedParameter(EventLog::CREATE)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'er.event',
                    $queryBuilder->createNamedParameter(EventLog::SUCCESS)
                )
            )
            ->groupBy('secret')
            ->execute();
        return $statement->fetchAll();
    }

    public function getUnreadSecrets(array $readSecretIDs)
    {
        $queryBuilder = clone $this->getPreparedQueryBuilder();
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_secret')
            ->where(
                $queryBuilder->expr()->notIn('uid', $readSecretIDs)
            )
            ->execute();
        return $statement->fetchAll();
    }

    public function getExistingSecrets()
    {
        $queryBuilder = clone $this->getPreparedQueryBuilder();
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_secret')
            ->execute();
        return $statement->fetchAll();
    }

    public function getDeletedSecrets()
    {
        $queryBuilder = clone $this->getPreparedQueryBuilder();
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_eventlog', 'eventlog')
            ->where(
                $queryBuilder->expr()->eq('event', $queryBuilder->createNamedParameter(EventLog::DELETE))
            )
            ->execute();
        return $statement->fetchAll();
    }

    public function getMostRecentEvents(int $n)
    {
        $queryBuilder = clone $this->getPreparedQueryBuilder();
        $queryBuilder->resetQueryParts();

        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_eventlog')
            ->orderBy('uid', 'DESC')
            ->setMaxResults($n);

        return $statement->execute()->fetchAll();
    }

    public function getStatistics()
    {
        $return = [
            'totalStatistic' => [0 => []],
            'unreadSecrets' => null,
            'readSecrets' => null,
            'existingSecrets' => null,
            'createdSecrets' => null,
            'deletedSecrets' => null,
            'mostRecentEvents' => null,
        ];

        $createdSecrets = $this->getCreatedSecrets();
        $return['createdSecrets'] = $createdSecrets;
        $return['totalStatistic'][0]['createdSecrets'] = count($createdSecrets);

        $readSecrets = $this->getReadSecrets();
        $return['readSecrets'] = $readSecrets;
        $return['totalStatistic'][0]['readSecrets'] = count($readSecrets);

        $readSecretIDs = [];
        foreach ($readSecrets as $value){
            if($value['secret']){
                $readSecretIDs[] = $value['secret'];
            }
        }
        $unreadSecrets = $this->getUnreadSecrets($readSecretIDs);
        $return['unreadSecrets'] = $unreadSecrets;
        $return['totalStatistic'][0]['unreadSecrets'] = count($unreadSecrets);

        $existingSecrets = $this->getExistingSecrets();
        $return['existingSecrets'] = $existingSecrets;
        $return['totalStatistic'][0]['existingSecrets'] = count($existingSecrets);

        $deletedSecrets = $this->getDeletedSecrets();
        $deletedSecretsCount = count($deletedSecrets);
        $return['deletedSecrets'] = $deletedSecrets;
        $return['totalStatistic'][0]['deletedSecrets'] = $deletedSecretsCount;

        $mostRecentEvents = $this->getMostRecentEvents(10);
        $return['mostRecentEvents'] = $mostRecentEvents;
        return $return;
    }
}