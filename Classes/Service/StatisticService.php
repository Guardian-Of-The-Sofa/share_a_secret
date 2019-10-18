<?php

namespace Hn\ShareASecret\Service;

use DateTime;
use DateTimeZone;
use Hn\ShareASecret\Domain\Model\EventLog;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class StatisticService
{
    /* @var QueryBuilder */
    private $preparedQueryBuilder;

    /* @var QueryBuilder */
    private $queryBuilder;

    public function __construct()
    {
        $this->initPreparedQueryBuilder();
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_shareasecret_domain_model_eventlog');
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

    public function getStartingPoints(int $unixTimestamp = null)
    {
        if ($unixTimestamp === null) {
            $unixTimestamp = (new \DateTime())->sub(new \DateInterval('P30D'))->getTimestamp();
        }
        $startingPoints = [
            'startTimestamp' => $unixTimestamp,
            EventLog::CREATE => 0,
            EventLog::DELETE => 0,
            EventLog::REQUEST => 0,
            EventLog::SUCCESS => 0,
            EventLog::NOTFOUND => 0,
        ];
        $this->queryBuilder->resetQueryParts();
        $statement = $this->queryBuilder
            ->count('*')
            ->addSelect('event')
            ->from('tx_shareasecret_domain_model_eventlog', 'eventlog')
            ->where(
                $this->queryBuilder->expr()->lt(
                    'date',
                    $this->queryBuilder->createNamedParameter($unixTimestamp)
                )
            )
            ->groupBy('event');
        $result = $statement->execute()->fetchAll();
        foreach ($result as $row) {
            $startingPoints[$row['event']] = $row['COUNT(*)'];
        }

        // set the count of existing secrets
        $existingSecrets = $startingPoints[EventLog::CREATE] - $startingPoints[EventLog::DELETE];
        $startingPoints['existingSecrets'] = $existingSecrets;

        // get the count of read secrets, not counting duplicates
        $this->queryBuilder->resetQueryParts();
        $statement = $this->queryBuilder
            ->select('*')
            ->from('tx_shareasecret_domain_model_eventlog', 'eventlog')
            ->where(
                $this->queryBuilder->expr()->eq(
                    'event',
                    $this->queryBuilder->createNamedParameter(EventLog::SUCCESS)
                )
            )
            ->andWhere(
                $this->queryBuilder->expr()->lt(
                    'date',
                    $this->queryBuilder->createNamedParameter($unixTimestamp)
                )
            )
            ->groupBy('secret');
        $readSecrets = count($statement->execute()->fetchAll());
        $startingPoints['readSecrets'] = $readSecrets;
        return $startingPoints;
    }

    private function getXValues(array $elements)
    {
        $return = [];
        foreach ($elements as $element) {
            $return[] = $element['date'];
        }
        return array_unique($return);
    }

    private function initYValues(array $xValues)
    {
        $return = [];
        foreach ($xValues as $x) {
            $return[$x] = 0;
        }
        return $return;
    }

    private function getEvents(array $elements, int $event)
    {
        $return = [];
        foreach ($elements as $element) {
            if ($element['event'] == $event) {
                $return[] = $element;
            }
        }
        return $return;
    }

    private function prepareYValuesForGraph(array $events,
                                            array $initializedYValues
    )
    {
//        debug($events, '$events');
        foreach ($events as $event) {
            if ($event) {
                $initializedYValues[$event['date']] += 1;
            }
        }
        return $initializedYValues;
    }

    private function prepareYValues(array $initializedYValues, array $elements)
    {
        //debug($elements, '$elements');
        $eventsGrouped = [];
        foreach (EventLog::getEventIDs() as $eventID) {
            $eventsGrouped[$eventID] = $this->getEvents($elements, $eventID);
        }
//        debug($eventsGrouped, '$eventsGrouped');

        $yValues = [];
        foreach (EventLog::getEventIDs() as $eventID) {
            $yValues[$eventID] = $this->prepareYValuesForGraph(
                $eventsGrouped[$eventID],
                $initializedYValues
            );
        }
//        debug($yValues, '$yValues');
        return $yValues;
    }

    public function createGraphData($preparedGraphData, $startingPoint)
    {
        $graphData = [];
        $yValue = $startingPoint;
        foreach ($preparedGraphData as $timestamp => $value) {
            $yValue += $preparedGraphData[$timestamp];
            $graphData[$timestamp] = $yValue;
        }
        return $graphData;
    }

    public function getExistingSecretsGraphData(int $existingSecrets,  array $preparedDeletedSecrets, array $preparedCreatedSecrets)
    {
        $existingSecretsGraph = [];
        foreach($preparedDeletedSecrets as $x => $y){
            $existingSecrets += $preparedCreatedSecrets[$x] - $preparedDeletedSecrets[$x];
            $existingSecretsGraph[$x] = $existingSecrets;
        }
        return $existingSecretsGraph;
    }

    /**
     * Crops a unixtime to 00:00,
     * i.e. a timestamp representing the time "1970.01.01 08:58"
     * gets cropped to "1970.01.01 00:00"
     *
     * IMPORTANT:
     * This method needs to be reconsidered due to timezones.
     *
     * @param int $unixtime
     * @return int
     * @throws \Exception
     */
    public function cropTime(int $unixtime)
    {
        $croppedDate = new DateTime(
            (new DateTime("@$unixtime", null))->format('Ymd'),
            new DateTimeZone('+0000') // Important!
        );
        return intval($croppedDate->format('U'));
    }

    public function cropTimeForElements(array $elements)
    {
        $return = [];
        foreach ($elements as $element) {
            $date = $element['date'];
            $element['date'] = $this->cropTime($date);
            $return[] = $element;
        }
        return $return;
    }

    public function insertMissingDays(array $days)
    {
        $currentUnixTime = $this->cropTime(time());
        $days[] = $currentUnixTime;

        sort($days);
        $first = array_shift($days);
        $last = array_pop($days);
        $days[] = $first;
        $days[] = $last;
        $newDay = $first;
        while($newDay < $last){
            $newDay += 24*60*60;
            if(array_search($newDay, $days, true) === false){
                $days[] = $newDay;
            }
        }
        sort($days);
        return array_unique($days);
    }

    public function getGraphData(array $startingPoints)
    {
        $startTimestamp = $startingPoints['startTimestamp'];
        // get x-values and initialize y-values
        $this->queryBuilder->resetQueryParts();
        $statement = $this->queryBuilder
            ->select('date', 'event')
            ->from('tx_shareasecret_domain_model_eventlog', 'eventlog')
            ->where(
                $this->queryBuilder->expr()->gte(
                    'date',
                    $this->queryBuilder->createNamedParameter($startTimestamp)
                )
            )
            ->orderBy('date', 'ASC')
            ->execute();
        $elements = $statement->fetchAll();

//        $result = [
//            EventLog::CREATE => [],
//            EventLog::DELETE => [],
//            EventLog::REQUEST => [],
//            EventLog::SUCCESS => [],
//            EventLog::NOTFOUND => [],
//        ];
//        $totals = [
//            EventLog::CREATE => 0,
//            EventLog::DELETE => 0,
//            EventLog::REQUEST => 0,
//            EventLog::SUCCESS => 0,
//            EventLog::NOTFOUND => 0,
//        ];
//        foreach ($elements as $element) {
//            $totals[$element['event']]++;
//            $result[$element['event']][$element['date']] = $totals[$element['event']];
//        }
//        $currentTime = time();
//        $result[EventLog::CREATE][$currentTime] = $totals[EventLog::CREATE];
//        $result[EventLog::DELETE][$currentTime] = $totals[EventLog::DELETE];
//        $result[EventLog::REQUEST][$currentTime] = $totals[EventLog::REQUEST];
//        $result[EventLog::SUCCESS][$currentTime] = $totals[EventLog::SUCCESS];
//        $result[EventLog::NOTFOUND][$currentTime] = $totals[EventLog::NOTFOUND];
//
//        return $result;

        $elements = $this->cropTimeForElements($elements);
        $xValues = $this->getXValues($elements);
        $xValues = $this->insertMissingDays($xValues);
        $initializedYValues = $this->initYValues($xValues);
        $preparedYValues = $this->prepareYValues($initializedYValues, $elements);
        $preparedGraphData = [];
        foreach (EventLog::getEventIDs() as $eventID) {
            $preparedGraphData[$eventID] = $preparedYValues[$eventID];
        }
        $graphData = $preparedGraphData;
//        $graphData = [];
//        foreach (EventLog::getEventIDs() as $eventID) {
//            $graphData[$eventID] = $this->createGraphData($preparedGraphData[$eventID],
//                $startingPoints[$eventID]
//            );
//        }
//        $graphData['existingSecrets'] = $this->getExistingSecretsGraphData(
//            $startingPoints['existingSecrets'],
//            $preparedYValues[EventLog::DELETE],
//            $preparedYValues[EventLog::CREATE]
//        );
        return $graphData;
    }

    public function formatData(array $data)
    {
        $return = [];
        foreach ($data as $x => $y){
            // The following doesn't work, unixtime is an absolute value (always UTC+0)
//            $x = (new DateTime("@$x", new DateTimeZone('Europe/Berlin')))->format('U');
            $return[] = [$x*1000, $y];
        }
        return $return;
    }

    public function formatGraphData(array $graphData)
    {
        $return = [];
        foreach ($graphData as $key => $value){
            $return[$key] = $this->formatData($value);
        }
        return $return;
    }

    /**
     * returns activity highchart chart configuration
     *
     * @return array
     */
    public function getActivityChartConfig()
    {
        $startingPoints = $this->getStartingPoints(0);
        $graphData = $this->getGraphData($startingPoints);
        $graphData = $this->formatGraphData($graphData);
        $series = [];
        foreach($graphData as $key => $value){
            $series[] = [
                'type' => 'spline',
                'name' => $this->translate("activity_chart_label.event.$key") ?? "$key",
                'data' => $value,
                'tooltip' => [
                    'valueDecimals' => 0,
                ]
            ];
        }
        return [
            'title' => [
                'text' => 'Activity chart'
            ],

            'series' => $series,

            'legend' => [
                'enabled' => true,
                'layout' => 'horizontal',
                'align' => 'left',
                'verticalAlign' => 'bottom'
            ],

            'yAxis' =>  [
                'title' =>  [
                    'text' =>  'Total events counted',
                ],
            ],

//            'time' => [
//                'timezoneOffset' => 0,
//            ],
        ];
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
        foreach ($readSecrets as $value) {
            if ($value['secret']) {
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

        $startingPoints = $this->getStartingPoints(0);//(new \DateTime())->getTimestamp());
//        $graphData = $this->getGraphData($startingPoints);
//        $return['graphData'] = $graphData;
        return $return;
    }

    private function translate($key) {
        return LocalizationUtility::translate("LLL:EXT:share_a_secret/Resources/Private/Language/lang_mod.xlf:$key");
    }
}