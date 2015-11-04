var InlineTranslator = {
    base: null,

    init: function(base) {
        this.base = base;
    },

    get(key) {
        return $.get(this.base + '/translation/' + key);
    },

    post(locale, key, translations) {
        return $.post(this.base + '/translation/' + locale + '/' + key, translations);
    },
};

var TranslatorPopup = {
    elem: null,
    inputs: [],

    open: function(elem, html) {
        var self = this;
        this.elem = elem;
        $(document.body).prepend($.parseHTML(html));
        this.inputs = $('#popup-translation input');

        $('#save-translation').on('click', function(e) {
            e.preventDefault();
            self.save();
        });

        $('#cancel-translation').on('click', function(e) {
            e.preventDefault();
            self.close();
        });

        $('.popup').on('click', function(e) {
            e.stopPropagation();
        });

        $(document).on('click', function(e) {
            self.close();
        });

        $(document).on('keydown', function(e) {
            if (e.which == 27) {
                self.close();
            }
        });

        $(this.inputs[0]).focus();
        $(this.inputs[0]).select();
    },

    save: function() {
        var data = {},
            self = this;

        $.each(this.inputs, function(key, input) {
            data[input.name] = input.value;
        });

        InlineTranslator.post(this.elem.data('locale'), this.elem.data('key'), {'translations' : data}).success(function(response) {
            $("." + self.elem.data('for')).text(response.translation);
            self.close();
        });
    },

    close: function() {
        $('#save-translation').off('click');
        $('#cancel-translation').off('click');
        $('.popup').off('click');
        $(document).off('keydown');

        this.inputs = [];
        this.elem = null;
        $('#popup-translation').remove();
    },
};

$(document).ready(function() {
    InlineTranslator.init('/l10n');

    $('.admin-translation').on('click', function(e) {
        var elem = $(this);
        e.stopPropagation();
        e.preventDefault();

        InlineTranslator.get(elem.data('key')).success(function(html) {
            TranslatorPopup.open(elem, html);
        });
    });
});
