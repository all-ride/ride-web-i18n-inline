$(document).ready(function() {

    $('.admin-translation').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var elem = $(this);

        $.get("/l10n/translation?key=" + elem.data('key'), function(data) {
            $(document.body).prepend($.parseHTML(data));
            var inputs = $('#popup-translation input');
            $(inputs[0]).focus();
            $(inputs[0]).select();

            $('#save-translation').on('click', function(e) {
                e.preventDefault();
                saveTranslation();
            });
            $('#cancel-translation').on('click', function(e) {
                e.preventDefault();
                hidePopup();
            });
            $('.popup').on('click', function(e) {
                e.stopPropagation();
            });
            $(document).on('click', function(e) {
                hidePopup();
            });
            $(document).on('keydown', function(e) {
                if (e.which == 27) {
                    hidePopup();
                }
            });

            function hidePopup() {
                $('#popup-translation').remove();
                $('#save-translation').off('click');
                $('#cancel-translation').off('click');
                $('.popup').off('click');
                $(document).off('keydown');
            }

            function saveTranslation() {
                var data = {};

                $.each(inputs, function(key, input) {
                    data[input.name] = input.value;
                });

                $.post("/l10n/translation/save?key=" + elem.data('key') + "&locale=" + elem.data('locale'), {'translations' : data}, function(current) {
                    $("."+elem.data('for')).text(current);
                    hidePopup();
                });
            }
        });
    });
});
