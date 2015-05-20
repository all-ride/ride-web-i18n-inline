$(document).ready(function() {

    $('.admin-translation').on('click', function(e) {
        if (!e.altKey) {
            return;
        }

        e.stopPropagation();
        e.preventDefault();
        var elem = $(this);

        $.get("/translation?key=" + elem.data('key'), function(data) {
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
            $(document).on('click', function() {
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

                $.post("/translations?key=" + elem.data('key') + "&locale=" + elem.data('locale'), {'translations' : data}, function(current) {
                    elem.text(current);
                    hidePopup();
                });
            }
        });
    });

    $('.admin-translation').on('mousemove', function(e) {
        if (!e.altKey) {
            return;
        }

        $(this).addClass('hovered');
    });

    $('.admin-translation').on('mouseleave', function(e) {
        $(this).removeClass('hovered');
    });

});
