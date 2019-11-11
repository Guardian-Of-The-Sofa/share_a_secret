function findMatches($rows, inputValue) {
    var matchedRows = [];
    try {
        var regExp = new RegExp(inputValue, "i");
        $rows.each(function (){
            var $row = $(this);
            var text = $row.text();
            if(regExp.test(text)){
                matchedRows.push($row);
            }
        });
    }catch(e){
        console.log('The regular expression \'' + inputValue + '\' is invalid')
    }
    return matchedRows;
}

function filterTable($tableBody, matchedRows){
    $tableBody.children(":not(:first-child)").hide();
    matchedRows.forEach(function($element){
        $element.show();
    });
}

require(['jquery'],
    function ($) {
        $("[name='search']").on("change input", function () {
            var $input = $(this);
            var inputValue = $input.val();
            var $table = $input.parents("div[class=\"table-view-helper\"]").children("table");
            var $rows = $table.find("tr:not(:first-child)");
            var matchedRows = findMatches($rows, inputValue);

            if(inputValue === ""){
                $table.find("tr:hidden").show();
                return;
            }

            filterTable($table.children("tbody"), matchedRows);
        });
    }
);
