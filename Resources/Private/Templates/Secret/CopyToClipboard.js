export function copyToClipboard(id){
    console.log("inside copyToClipboard()");
    console.log('id=' + id);
    let $textfield = $('#' + id);
    console.log($textfield);
    $textfield.select();
    document.execCommand('copy');
    console.log("leaving copyToClipboard()");
}
