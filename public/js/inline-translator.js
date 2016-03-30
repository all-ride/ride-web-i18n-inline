
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
        return $.post(this.base + '/translation/' + locale + '/' + key, translations);
    },
};

var Translation = {
    'key': null,
    'currectLocale': null,

    'create': function(el) {
        var translation = Object.create(Translation);

        translation.init(el);

        return translation;
    },

    'init': function(el) {
        this.key = el.getAttribute('data-translation-key');
        this.currectLocale = el.getAttribute('data-locale');
        // InlineTranslatorAPI.get(this.key).then(function())
    },

    'saveTranslation': function(values) {
        InlineTranslatorAPI.post(this.currentLocale, this.key, values).then(function() {
            $('mark.inline_translation[data-translation-key="' + this.key + '"]').text(values[this.currentLocale]);
        });
    },

    'getForm': function() {
        // TODO: create the form for this translation
        // TODO: add event listener to save the translation
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
        this.el = $('<div class="translation_list"><ul></ul><div class="translation_form"><div class="translation_form--input"></div></div></div>');
        var $translationList = this.el.find('ul');

        $.each(this.translations, function(k, translation) {
            var $translationListItem = $('<li></li>');

            $translationListItem.html(translation.value + '<small>' + translation.key + '</small>');
            $translationListItem.attr('data-translation-key', translation.key);
            $translationList.append($translationListItem);

            $translationListItem.on('mouseenter', function() {
                $('mark.inline_translation[data-translation-key="' + translation.key + '"]').addClass('inline_translation--active');
            });

            $translationListItem.on('mouseleave', function() {
                $('mark.inline_translation[data-translation-key="' + translation.key + '"]').removeClass('inline_translation--active');
            });

            $translationListItem.on('click', function() {
                InlineTranslatorAPI.get(this.getAttribute('data-translation-key')).then(function(json) {
                    $('.translation_list .translation_form .translation_form--input').empty();

                    $.each(json, function(locale, data) {
                        // TODO create form
                    });
                });
            });

        });

        $('body').append(this.el);
    },

};
