<?php

namespace Hn\ShareASecret\ViewHelpers;

use Hn\ShareASecret\Utility\ArrayModifier;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class TableViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    protected static $columnFormats;

    public function initializeArguments()
    {
        $this->registerArgument('elements', 'array', 'The elements to display as rows', true);
        $this->registerArgument('columns', 'array', 'The columns to display', false);
        $this->registerArgument('columnNames', 'array', 'An array of column names as key and their label as a value', false);
        $this->registerArgument('tableHeading', 'string', 'The heading to display', false, '');
        $this->registerArgument('ordering', 'array', 'The ordering in which to display the elements', false);
        $this->registerArgument('formats', 'array', 'The Formats in which to display columns. Currently only supporting columns which display dates', false);
        $this->registerArgument('tableClass', 'string', 'A string containing all classes this table should belong to', false);
        $this->registerArgument('top', 'int', 'The number of elements to display from the top', false);
        $this->registerArgument('excludeNull', 'array', 'An array containing column names in which to delete every null value', false);
        $this->registerArgument('excludeColumns', 'array', 'An array containing column names to exclude from table', false);
        $this->registerArgument('searchable', 'bool', 'Renders a search input box to the table', false, true);
    }

    public static function formatValue(string $column, $value)
    {
        if($value === null){
            return '';
        }
        $columnFormat = self::$columnFormats[$column] ?? null;
        if(!$columnFormat){
            return $value;
        }
        $type = key($columnFormat);
        $format = $columnFormat[$type];
        if($type == 'date'){
            $date = new \DateTime();
            $date->setTimestamp($value);
            $value = $date->format($format);
        }

        return $value;
    }

    public static function mapOrdering(array $ordering)
    {
        $return = [];
        foreach ($ordering as $key => $value){
            if($value == 'ASC'){
                $value = ArrayModifier::ASC;
            }else{
                $value = ArrayModifier::DESC;
            }
            $return[$key] = $value;
        }
        return $return;
    }

    private static function excludeColumns(array& $columns, array $excludeColumns)
    {
        $columns = array_diff($columns, $excludeColumns);
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    )
    {
        $return = '';
        self::$columnFormats = $arguments['formats'] ?? [];
        $tableHeading = $arguments['tableHeading'] ?? '';
        $tableClass = $arguments['tableClass'] ?? '';
        $ordering = $arguments['ordering'] ?? false;
        $top = $arguments['top'] ?? false;
        $columns = $arguments['columns'] ?? false;
        $elements = $arguments['elements'];
        $excludeNull = $arguments['excludeNull'] ?? false;
        $excludeColumns = $arguments['excludeColumns'] ?? false;
        $columnNames = $arguments['columnNames'] ?? false;
        $searchable = $arguments['searchable'];

        if(!$elements){
            return '';
        }

        if(!$columns){
            $element = $elements[0];
            foreach ($element as $column => $value){
                $columns[$column] = '';
            }
            foreach ($columns as $column => $name){
                $columns[$column] = $columnNames[$column] ?? $column;
            }
        }

        if($excludeColumns){
            self::excludeColumns($columns, $excludeColumns);
        }

        // sort array
        if($ordering){
            $ordering = self::mapOrdering($ordering);
            $elements = ArrayModifier::sortByProperties($elements, $ordering);
        }

        // slice array
        if($top){
            $elements = array_slice($elements, 0, $top);
        }

        // remove null values
        if($excludeNull){
            foreach ($excludeNull as $property){
                $elements = ArrayModifier::getByNonNullProperty($elements, $property);
            }
        }

        $return = '<div class="table-view-helper">';
        if($searchable){
            $return .= "<span class=\"search\">search: <input type=\"text\" name=\"search\" size=\"20\"></span>";
            $tableClass .= " searchable";
        }
        $return .=  "<table class=\"table $tableClass\">";
        // create table heading
        $return .= "<caption>$tableHeading</caption>";

        // create table head
        $return .= '<tr>';
        foreach ($columns as $column => $name){
            $return .= "<th scope=\"col\" data-column-name=\"$column\" sorting=\"both\">$name</th>";
        }
        $return .= '</tr>';

        // create table rows
        foreach ($elements as $element){
            $return .= '<tr>';
            $i = 0;
            foreach ($columns as $column => $name){
                $value = $element[$column];
                if(self::$columnFormats[$column]){
                    $rawAttribute = "data-raw=\"$value\"";
                }else{
                    $rawAttribute = '';
                }

                if($i == 0){
                    $return .= "<th scope=\"row\" data-column-name=\"$column\" $rawAttribute>" . self::formatValue($column, $value) . '</th>';
                    $i++;
                }else{
                    $return .= "<td data-column-name=\"$column\" $rawAttribute>" . self::formatValue($column, $value) . '</td>';
                }
            }
            /*
            foreach ($columns as $column => $name){
                $value = ObjectAccess::getProperty($element, $column);
                if($i == 0){
                    $return .= '<th scope="row">' . self::formatValue($column, $value) . '</th>';
                    $i++;
                }else{
                    $value = ObjectAccess::getProperty($element, $column);
                    $return .= '<td>' . self::formatValue($column, $value) . '</td>';
                }
            }
            */
            $return .=  '</tr>';
        }
        $return .= '</table>';
        $return .= '</div>';
        return $return;
    }
}
