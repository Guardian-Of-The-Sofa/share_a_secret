function copyToClipboard(id){
    let $textfield = $('#' + id);
    $textfield.select();
    document.execCommand('copy');
}

$('.copy-to-clipboard').each(function (index, element) {
    // element === this
    $(element).on('click', function () {
        copyToClipboard(element.getAttribute('data-input-text-id'));
    });
});
