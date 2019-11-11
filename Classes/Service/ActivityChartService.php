<?php

namespace Hn\ShareASecret\Service;

use DateTime;
use DateTimeZone;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ActivityChartService
{
    /* @var StatisticService */
    private $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    public function formatData(array $data)
    {
        $return = [];
        foreach ($data as $x => $y){
            $return[] = [$x*1000, $y];
        }
        return $return;
    }

    /**
     * converts seconds to milliseconds, highcharts demands this
     */
    public function formatGraphs(array $graphData)
    {
        $return = [];
        foreach ($graphData as $event => $graph){
            $return[$event] = $this->formatData($graph);
        }
        return $return;
    }

    public function initializeYValues($xValues)
    {
        $newXY = [];
        foreach ($xValues as $x){
            $newXY[$x] = 0;
        }
        return $newXY;
    }

    public function cropXValues($xValues)
    {
        $return = [];
        foreach ($xValues as $x){
            $croppedX = $this->cropTime($x);
            if(array_search($croppedX, $return) === false){
                $return[] = $croppedX;
            }
        }
        return $return;
    }

    public function setYValuesForDays(array $graph, array $xY)
    {
        foreach ($graph as $x => $y){
            $croppedX = $this->cropTime($x);
            $xY[$croppedX] += $y;
        }
        return $xY;
    }

    public function getXValuesFromGraphs(array $graphs)
    {
        $xValues = [];
        foreach ($graphs as $event => $graph){
            if(count($graph) != 0){
                $xValues = array_keys($graph);
                break;
            }
        }
        return $xValues;
    }

    /**
     * @param array $graphData
     * @return array
     */
    public function prepareGraphs(array $graphData)
    {
        $xValues = $this->getXValuesFromGraphs($graphData);
        $xValues[] = time();
        $days = $this->cropXValues($xValues);
        $days = $this->insertMissingDays($days);
        $days = $this->initializeYValues($days);
        foreach ($graphData as $event => $graph){
            $graphData[$event] = $this->setYValuesForDays($graph, $days);
        }
        return $graphData;
    }

    private function translate($key) {
        return LocalizationUtility::translate("LLL:EXT:share_a_secret/Resources/Private/Language/lang_mod.xlf:$key");
    }

    public function insertMissingDays(array $days)
    {
        $currentDay = $this->cropTime(time());
        $days[] = $currentDay;

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
        $offsetSec = (new DateTime("now"))->getOffset();
        $unixtime += $offsetSec;

        $croppedDate = new DateTime(
            (new DateTime("@$unixtime", null))->format('Ymd'),
            new DateTimeZone('+0000') // Important! Otherwise the current timezone
                                              // offset gets substracted on the conversion ->format('U') below
        );
        return intval($croppedDate->format('U'));
    }

    /**
     * returns activity highchart chart configuration
     *
     * @return array
     */
    public function getActivityChartConfig()
    {
        $startingPoints = $this->statisticService->getStartingPoints(0);
        $eventGraphs = $this->statisticService->getEventGraphs($startingPoints);
        $eventGraphs = $this->prepareGraphs($eventGraphs);
        $eventGraphs = $this->formatGraphs($eventGraphs);
        $series = [];
        foreach($eventGraphs as $key => $value){
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
        ];
    }
}