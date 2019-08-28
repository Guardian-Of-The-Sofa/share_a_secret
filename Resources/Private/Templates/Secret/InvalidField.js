import * as cb from './CopyToClipboard.js';
$(function (){
    console.log('inside document.read()');
    let $invalidField = $('.is-invalid');
    $invalidField.keydown(function(){
        $invalidField.removeClass('is-invalid');
    });

    let $textForm = $('#copy-to-clipboard');
    console.log($textForm);
    $textForm.click({id: $textForm.data('input-text-id')}, function (event) {
        cb.copyToClipboard(event.data.id);
    });
    console.log('leaving document.read()');
});