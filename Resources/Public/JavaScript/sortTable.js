function sortNumbers(a, b){
    return a-b;
}

function isArrayOfInts(array) {
    var regExp = /^\d*$/;
    var isArrayOfInts = true;
    for(let i = 0; i < array.length; i++){
        if(!regExp.test(array[i])){
            isArrayOfInts = false;
            break;
        }
    }
    return isArrayOfInts;
}

function convertToInts(array) {
    var ret = [];
    array.forEach(function (element) {
        ret.push(parseInt(element));
    });
    return ret;
}

function moveNaN(array){
    var ret = [];
    for(var i = 0; i < array.length; i++){
        var number = array[i];
        if(isNaN(number)){
            ret.unshift(number);
        }else{
            ret.push(number);
        }
    }
    return ret;
}

function determineOrder(sortingOrder){
    if(sortingOrder === undefined){
        sortingOrder = "asc";
    }else{
        if(sortingOrder === "asc"){
            sortingOrder = "desc";
        }else{
            sortingOrder = "asc";
        }
    }
    return sortingOrder;
}

function getColumnValues($rows, columnName){
    var columnValues = [];
    $rows.each(function (index, element) {
        var $row = $(this);
        var $column = $row.children("[data-column-name='" + columnName + "']");
        var columnValue = $column.attr("data-raw");
        if(columnValue === undefined) {
            columnValue = $column.text();
        }
        columnValues.push(columnValue);
    });
    return columnValues;
}

function convert(array){
    if(isArrayOfInts(array)){
        array = convertToInts(array);
    }
    return array;
}

function getColumnValue($column){
    var columnValue = $column.attr("data-raw");
    if(columnValue === undefined) {
        columnValue = $column.text();
    }
    return columnValue;
}

function convertToStrings(array){
    var ret = [];
    array.forEach(function (element) {
        ret.push(element.toString());
    });
    return ret;
}

function sortArray(array, sortingOrder){
    if(isArrayOfInts(array)){
        array = convertToInts(array);
        array = moveNaN(array);
        array.sort(sortNumbers);
        array = convertToStrings(array);
    }else{
        array.sort();
    }
    if(sortingOrder === 'desc'){
        array.reverse();
    }
    return array;
}

function manipulateDOM($tableBody, columnValues, valueRowsMap){
    $tableBody.children().remove(":not(:first-child)");
    columnValues.forEach(function(element){
        if(element === "NaN"){
            element = "";
        }
        var rowsArr = valueRowsMap.get(element.toString());
        $tableBody.append(rowsArr);
    });
}
function changeColumnClass($columnHead, sortingOrder){
    var $columnHeads = $columnHead.parent().children();
    // reset sorting value
    $columnHeads.each(function (index, element){
        $child = $(this);
        $child.attr("sorting", "both");
    });
    $columnHead.attr("sorting", sortingOrder);
}

require([
    'jquery'
], function ($) {
    $(document).on("click", "th[scope='col']", function (event) {
        var $columnHead = $(this);
        var $headerRow = $columnHead.parent();
        var columnName = $columnHead.attr("data-column-name");
        var sortingOrder = $columnHead.attr("data-sorting-order");
        var $tableBody = $headerRow.parent();
        var $table = $tableBody.parent();
        var $rows = $tableBody.children(":not(:first-child)");

        if(!$table.hasClass("sortable")){
            return;
        }

        sortingOrder = determineOrder(sortingOrder);
        changeColumnClass($columnHead, sortingOrder);
        $columnHead.attr("data-sorting-order", sortingOrder.toLowerCase());

        var columnValues = new Set();
        var valueRowsMap = new Map();
        $rows.each(function (index, element) {
            var $row = $(this);
            var $column = $row.children("[data-column-name='" + columnName + "']");
            var columnValue = getColumnValue($column);

            var targetArray = valueRowsMap.get(columnValue);
            if(targetArray === undefined){
                valueRowsMap.set(columnValue, [$row]);
            }else{
                targetArray.push($row);
            }
            columnValues.add(columnValue);
        });
        columnValues = [...columnValues];
        columnValues = sortArray(columnValues, sortingOrder);
        manipulateDOM($tableBody, columnValues, valueRowsMap);
    });
});
