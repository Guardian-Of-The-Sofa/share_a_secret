(function (){
    let $characterCountField = $('#character-count');
    let maxCharacters = $characterCountField.data('maxLength')
    let $messageField = $('#message');
    let currentCount = $messageField.val().length;
    let $formButton = $('.btn[type="submit"]');

    $messageField.on('input', onMessageChange);

    if(currentCount > maxCharacters){
        $characterCountField.css('color', 'red');
        disableButton(true);
    }

    function disableButton(bool){
        $formButton.prop('disabled', bool);
    }

    function onMessageChange() {
        let currentCount = $(this).val().length;
        $characterCountField.html(currentCount);
        if(currentCount > maxCharacters){
            $characterCountField.css('color', 'red')
            disableButton(true);
        } else {
            $characterCountField.css('color', 'initial')
            disableButton(false);
        }
    }
})();
