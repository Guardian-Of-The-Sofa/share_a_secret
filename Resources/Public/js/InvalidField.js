$('.sas-is-invalid').each(function(index, element) {
    $(element).on('input', function() {
        $(element).removeClass('sas-is-invalid').siblings('.sas-invalid-feedback').hide();
    });
});