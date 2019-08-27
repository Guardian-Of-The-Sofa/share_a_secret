$(function (){
    var $invalidField = $('.is-invalid');
    $invalidField.keydown(function(){
        $invalidField.removeClass('is-invalid');
    });
});