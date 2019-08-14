$link = $("#secret-link");
$textArea = $('#text-area-link');
$textAreaText = $textArea.html();
$textArea.html($textAreaText + $link.attr('href'));
