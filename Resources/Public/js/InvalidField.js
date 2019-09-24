$('.is-invalid').each(function(index, element) {
    $(element).on('input', function() {
        $(element).removeClass('is-invalid').siblings('.invalid-feedback').hide();
    });
});