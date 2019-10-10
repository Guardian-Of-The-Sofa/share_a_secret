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

    public function __construct(EventLogRepository $eventLogRepository)
    {
        $this->eventLogRepository = $eventLogRepository;
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

    public function getStatistics()
    {
        $return = [
            'totalStatistic' => [0 => []],
            'unreadSecrets' => null,
            'readSecrets' => null,
            'existingSecrets' => null,
            'createdSecrets' => null,
            'deletedSecrets' => null,
        ];

        /**
         * get all created secrets
         */
        /* @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_shareasecret_domain_model_eventlog');
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_eventlog')
            ->where(
                $queryBuilder->expr()->eq(
                    'event',
                    $queryBuilder->createNamedParameter(EventLog::CREATE)
                )
            )
            ->execute();
        $createdSecrets = $statement->fetchAll();
        $createdSecretsCount = count($createdSecrets);
        $return['totalStatistic'][0]['createdSecretsCount'] = $createdSecretsCount;
        $return['createdSecrets'] = $createdSecrets;

        /**
         * get all secrets which have been read
         */
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select(
                'eventlog.secret AS secretID',
                        'secret.crdate AS creationDate',
                        'eventlog.date AS readDate'
            )
            ->from('tx_shareasecret_domain_model_secret', 'secret')
            ->rightJoin(
                'secret',
                'tx_shareasecret_domain_model_eventlog',
                'eventlog',
                $queryBuilder->expr()->eq(
                    'secret.uid',
                    $queryBuilder->quoteIdentifier('eventlog.secret')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'event',
                    $queryBuilder->createNamedParameter(EventLog::SUCCESS)
                )
            )
            ->groupBy('secret')
            ->execute();
        $readSecrets = $statement->fetchAll();
        $readSecretIDs = [];
        foreach ($readSecrets as $value){
            if($value['secretID']){
                $readSecretIDs[] = $value['secretID'];
            }
        }
        $return['readSecrets'] = $readSecrets;
        $return['totalStatistic'][0]['readSecrets'] = count($return['readSecrets']);

        /**
         * get all unread secrets
         **/
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_secret')
            ->where(
                $queryBuilder->expr()->notIn('uid', $readSecretIDs)
            )
            ->execute();
        $unreadSecrets = $statement->fetchAll();
        $return['totalStatistic'][0]['unreadSecrets'] = count($unreadSecrets);
        $return['unreadSecrets'] = $unreadSecrets;

        /**
         * Get all secrets in the database
         */
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_secret')
            ->execute();
        $existingSecrets = $statement->fetchAll();
        $return['existingSecrets'] = $existingSecrets;

        /**
         * Get all deleted secrets
         */
        $queryBuilder->resetQueryParts();
        $statement = $queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_eventlog', 'eventlog')
            ->where(
                $queryBuilder->expr()->eq('event', $queryBuilder->createNamedParameter(EventLog::DELETE))
            )
            ->execute();
        $deletedSecrets= $statement->fetchAll();
        $deletedSecretsCount = count($deletedSecrets);
        $return['totalStatistic'][0]['deletedSecrets'] = $deletedSecretsCount;
        $return['deletedSecrets'] = $deletedSecrets;

        return $return;
    }
}