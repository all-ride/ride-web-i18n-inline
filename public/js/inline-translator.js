
$(document).ready(function() {
    // Initialize the translator
    InlineTranslatorAPI.init('/api/v1/i18n');
    TranslationCollection.init();
});

/**
 * Translator service to communicate with the API
 */
var InlineTranslatorAPI = {

    /**
     * @var {string} base The API base URL
     */
    base: null,

    /**
     * Initialize the Inline Translator API service
     *
     * @param {string} base The API base URL
     */
    init: function(base) {
        this.base = base;
    },

    /**
     * Get the popup HTML for a given translation key
     *
     * @param {string} key The translation key
     *
     * @return {Promise}
     */
    get(key) {
        return $.get(this.base + '/translation/' + key);
    },

    /**
     * Post translations data
     *
     * @param {string} locale The current locale
     * @param {string} key The translation key
     * @param {object} translations An object with locale keys and its translation
     *
     * @return {Promise}
     */
    post(locale, key, translations) {
        return $.post(this.base + '/translation/' + locale + '/' + key, {"translations": translations});
    },
};

var Translation = {
    'key': null,
    'currentLocale': null,
    'text': null,

    'create': function(el) {
        var translation = Object.create(Translation);

        translation.init(el);

        return translation;
    },

    'init': function(el) {
        this.key = el.getAttribute('data-translation-key');
        this.currentLocale = el.getAttribute('data-locale');
        this.text = el.innerHTML;
    },

    'save': function(values) {
        var self = this;

        InlineTranslatorAPI.post(this.currentLocale, this.key, values).then(function() {
            var text = values[self.currentLocale];

            $('mark.inline_translation[data-translation-key="' + self.key + '"]').text(text);
            $('.translation_list li[data-translation-key="' + self.key + '"] span').text(text);
        });
    },

    'edit': function() {
        InlineTranslatorAPI.get(this.key).then(function(json) {
            var $form = $('.translation_form'),
                $rows = $('.translation_form--rows'),
                $row;

            $.each(json, function(locale, data) {
                $row = $('<div><label for="'+locale+'-'+data.key+'">'+locale+'</label><input type="text" id="'+locale+'-'+data.key+'" data-locale="'+locale+'"/></div>');
                if (data.translation) {
                    $row.find('input').val(data.translation);
                }

                $rows.append($row);
            });

            $form.addClass('active');
        });
    },
};

var TranslationCollection = {
    'translations': {},
    'el': null,

    'init': function() {
        var translation = null,
            self = this;

        $('mark.inline_translation').each(function(k, el) {
            translation = Translation.create(el);

            self.translations[translation.key] = translation;
        });

        this.render();
    },

    'render': function() {
        this.el = $('<div class="translation_list"><ul></ul></div>');
        var self = this,
            $translationList = this.el.find('ul'),
            $form = $('<div class="translation_form"><div class="translation_form--rows"></div>'),
            $rows = $form.find('.translation_form--rows'),
            $save = $('<a href="#" class="translation_form--save btn">Save</a>');

        $.each(this.translations, function(k, translation) {
            var $translationListItem = $('<li></li>');

            $translationListItem.html('<span>' + translation.text + '</span>' + '<small>' + translation.key + '</small>');
            $translationListItem.attr('data-translation-key', translation.key);
            $translationList.append($translationListItem);

            $translationListItem.on('mouseenter', function() {
                $('mark.inline_translation[data-translation-key="' + translation.key + '"]').addClass('inline_translation--active');
            });

            $translationListItem.on('mouseleave', function() {
                $('mark.inline_translation[data-translation-key="' + translation.key + '"]').removeClass('inline_translation--active');
            });

            $translationListItem.on('click', function() {
                $form.data('translation-key', translation.key);
                $rows.empty();

                translation.edit();
            });
        });

        $form.append($save);
        $translationList.append($form);

        $save.on('click', function(e) {
            e.preventDefault();

            var translation = self.translations[$form.data('translation-key')],
                values = {};

            $.each($rows.find('input'), function(k, row) {
                values[row.getAttribute('data-locale')] = row.value;
            });

            translation.save(values);

            $form.removeClass('active');
        });

        $('mark.inline_translation').on('click', function(e) {
            if (!e.altKey) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            var translation = self.translations[this.getAttribute('data-translation-key')];
            $form.data('translation-key', translation.key);
            $rows.empty();

            translation.edit();
        });

        document.addEventListener('keydown', function(e) {
            if (!e.altKey) {
                return;
            }

            $('mark.inline_translation').addClass('pointer');
        });

        document.addEventListener('keyup', function(e) {
            $('mark.inline_translation.pointer').removeClass('pointer');
        });

        $('body').append(this.el);
    },

};
