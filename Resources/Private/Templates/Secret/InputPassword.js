$(function (){
    var $textfield = $('#userPassword');
    $textfield.keydown(function(){
        $textfield.removeClass('is-invalid');
    });
});