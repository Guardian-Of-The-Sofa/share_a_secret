function sortNumbers(a, b) {
    return a - b;
}

function isArrayOfInts(array) {
    var regExp = /^\d*$/;

    for (let i = 0; i < array.length; i++) {
        if (!regExp.test(array[i])) {
            return false;
        }
    }
    return true;
}

function convertToInts(array) {
    var ret = [];
    array.forEach(function (element) {
        ret.push(parseInt(element));
    });
    return ret;
}

function moveNaN(array) {
    var ret = [];
    for (var i = 0; i < array.length; i++) {
        var number = array[i];
        if (isNaN(number)) {
            ret.unshift(number);
        } else {
            ret.push(number);
        }
    }
    return ret;
}

function determineOrder(sortingOrder) {
    return sortingOrder === 'asc' ? 'desc' : 'asc';
}

function getColumnValues($rows, columnName) {
    var columnValues = [];
    $rows.each(function (index, element) {
        var $row = $(this);
        var $column = $row.children("[data-column-name='" + columnName + "']");
        var columnValue = $column.attr("data-raw");
        if (columnValue === undefined) {
            columnValue = $column.text();
        }
        columnValues.push(columnValue);
    });
    return columnValues;
}

function convert(array) {
    if (isArrayOfInts(array)) {
        array = convertToInts(array);
    }
    return array;
}

function getColumnValue($column) {
    var columnValue = $column.attr("data-raw");
    if (columnValue === undefined) {
        columnValue = $column.text();
    }
    return columnValue;
}

function sortArray(array, sortingOrder) {
    array.sort(function(a, b) {
        if(isNaN(a)) {
            return (a).localeCompare(b);
        }
        return a - b;
    });
    if (sortingOrder === 'desc') {
        array.reverse();
    }
    return array;
}

function manipulateDOM($tableBody, columnValues, valueRowsMap) {
    $tableBody.children().remove();
    columnValues.forEach(function (element) {
        $tableBody.append(valueRowsMap[element]);
    });
}

function changeColumnSortingAttribute($columnHead, sortingOrder) {
    var $columnHeads = $columnHead.parent().children();
    // reset sorting value
    $columnHeads.each(function (index, element) {
        $(element).attr("data-sorting-order", "both");
    });
    $columnHead.attr("data-sorting-order", sortingOrder.toLowerCase());
}

require([
    'jquery'
], function ($) {
    $('table.sortable').each(function (index, el) {
        var $table = $(el);
        $table.on('click', 'th[scope="col"]', function (e) {
            var $columnHead = $(e.target);
            var $headerRow = $columnHead.parent();
            var columnIndex = $columnHead.index();
            var sortingOrder = $columnHead.attr("data-sorting-order");
            var $tableHead = $headerRow.parent();
            var $tableBody = $tableHead.siblings("tbody");
            var $rows = $tableBody.children();

            sortingOrder = determineOrder(sortingOrder);
            changeColumnSortingAttribute($columnHead, sortingOrder);

            var columnValues = {};
            var valueRowsMap = {};
            $rows.each(function (index, element) {
                var $row = $(element);
                var $column = $($row.children().get(columnIndex));
                var columnValue = getColumnValue($column);

                columnValues[columnValue] = true;
                if(valueRowsMap[columnValue] === undefined) {
                    valueRowsMap[columnValue] = [$row];
                } else {
                    valueRowsMap[columnValue].push($row);
                }
            });
            columnValues = Object.keys(columnValues);
            columnValues = sortArray(columnValues, sortingOrder);
            manipulateDOM($tableBody, columnValues, valueRowsMap);
        });
    });
});
