function copyToClipboard(id){
    console.log("inside copyToClipboard()");
    console.log('id=' + id);
    let $textfield = $('#' + id);
    console.log($textfield);
    $textfield.select();
    document.execCommand('copy');
    console.log("leaving copyToClipboard()");
}

$('.copy-to-clipboard').each(function (index, element) {
    // element === this
    $(element).on('click', function () {
        copyToClipboard(element.getAttribute('data-input-text-id'));
    })

});