function findMatches($rows, inputValue) {
    var matchedRows = [];
    try {
        var regExp = new RegExp(inputValue, "i");
    }catch(e){
        console.log('The regular expression \'' + inputValue + '\' is invalid')
        return $rows.toArray();
    }
    for(var i = 0; i < $rows.length; i++){
        var $row = $($rows.get(i));
        var $cells = $row.children();
        for(var j = 0; j < $cells.length; j++){
            var $cell = $($cells.get(j));
            var text = $cell.text();
            if(regExp.test(text)){
                matchedRows.push($row);
                break;
            }
        }
    }
    return matchedRows;
}

function filterTable($tableBody, matchedRows){
    $tableBody.children().hide();
    matchedRows.forEach(function(element){
        $(element).show();
    });
}

require(['jquery'],
    function ($) {
        $("div.table-view-helper [name='search']").on("input", function () {
            var $input = $(this);
            var inputValue = $input.val();
            var $table = $input.parents("div.table-view-helper").children("table");
            var $tbody = $table.children("tbody");
            var $rows = $tbody.children();
            var $matchedRows = findMatches($rows, inputValue);

            if(inputValue === ""){
                $table.find("tr:hidden").show();
                return;
            }

            filterTable($tbody, $matchedRows);
        });
    }
);
