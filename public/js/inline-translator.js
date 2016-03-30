
document.addEventListener('DOMContentLoaded', function() {
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

/**
 * A translation
 * @type {Object}
 */
var Translation = {
    /**
     * @type {String}
     */
    'key': null,

    /**
     * @type {String}
     */
    'currentLocale': null,

    /**
     * @type {String}
     */
    'text': null,

    /**
     * Create and return a new Translation from a DOM element
     * @param  {DOMElement} el
     * @return {Translation}
     */
    'create': function(el) {
        var translation = Object.create(Translation);

        translation.init(el);

        return translation;
    },

    /**
     * Initialize a new Translation
     * @param  {DOMElement} el
     */
    'init': function(el) {
        this.key = el.getAttribute('data-translation-key');
        this.currentLocale = el.getAttribute('data-locale');
        this.text = el.innerHTML;
    },

    /**
     * Save this translation
     * @param  {object} values
     * @return {Promise}
     */
    'save': function(values) {
        return InlineTranslatorAPI.post(this.currentLocale, this.key, values);
    },

    'highlight': function(enable) {
        if (enable === undefined || enable) {
            $('mark.inline_translation[data-translation-key="' + this.key + '"]').addClass('inline_translation--active');
        } else {
            $('mark.inline_translation[data-translation-key="' + this.key + '"]').removeClass('inline_translation--active');
        }
    }
};

var TranslationCollection = {
    'translations': {},
    'el': null,
    'form': null,
    'rows': null,
    'translationEdit': null,
    'promise': null,

    'init': function() {
        var translation = null,
            $labels = $('mark.inline_translation'),
            self = this;

        if (!$labels.length) {
            return;
        }

        $labels.each(function(k, el) {
            translation = Translation.create(el);

            self.translations[translation.key] = translation;
        });

        document.addEventListener('keydown', function(e) {
            if (!e.altKey) {
                return;
            }

            $labels.addClass('pointer');
        });

        document.addEventListener('keyup', function(e) {
            $labels.removeClass('pointer');
        });

        $('mark.inline_translation').on('click', function(e) {
            if (!e.altKey) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            self.openForm(self.translations[this.getAttribute('data-translation-key')]);
        });

        $('body').append(this.renderList());
    },

    'renderList': function() {
        this.el = $('<div class="translation_list"><ul></ul></div>');

        var self = this,
            $translationList = this.el.find('ul');

        $.each(this.translations, function(k, translation) {
            var $translationListItem = $('<li></li>');

            $translationListItem.html('<span>' + translation.text + '</span>' + '<small>' + translation.key + '</small>');
            $translationListItem.attr('data-translation-key', translation.key);
            $translationList.append($translationListItem);

            $translationListItem.on('mouseenter', function() {
                translation.highlight();
            });

            $translationListItem.on('mouseleave', function() {
                translation.highlight(false);
            });

            $translationListItem.on('click', function() {
                $translationListItem.off('mouseleave');
                self.openForm(translation);
            });
        });

        this.el.append(this.renderForm());

        return this.el;
    },

    'renderForm': function() {
        var self = this,
            $save = $('<a href="#" class="translation_form--save btn">Save</a>'),
            $cancel = $('<a href="#" class="translation_form--cancel">Cancel</a>');

        this.form = $('<div class="translation_form"><h3></h3><div class="translation_form--rows"></div><div class="translation_form--actions"></div></div>');
        this.rows = this.form.find('.translation_form--rows');
        this.formActions = this.form.find('.translation_form--actions');

        this.formActions.append($save);
        this.formActions.append($cancel);

        $save.on('click', function(e) {
            e.preventDefault();
            self.saveForm(self.translations[self.form.data('translation-key')])
        });

        $cancel.on('click', function(e){
            e.preventDefault();
            self.closeForm();
        });

        return this.form;
    },

    'openForm': function(translation) {
        if (!translation) {
            return;
        }

        this.promise = $.Deferred();

        var self = this,
            key = translation.key;

        this.form.data('translation-key', key);
        this.form.find('h3').text(key);
        this.rows.empty();

        InlineTranslatorAPI.get(key).then(function(json) {
            self.translationEdit = translation;
            self.promise.resolve();
            var $row;

            $.each(json, function(locale, data) {
                $row = $('<div><label for="'+locale+'-'+data.key+'">'+locale+'</label><input type="text" id="'+locale+'-'+data.key+'" data-locale="'+locale+'"/></div>');
                if (data.translation) {
                    $row.find('input').val(data.translation);
                }

                $row.on('keydown', function(e) {
                    if (e.keyCode === 13) {
                        self.saveForm(translation);
                    } else if (e.keyCode === 27) {
                        self.closeForm();
                    }
                });

                self.rows.append($row);
            });

            self.el.addClass('edit');
            self.rows.find('input').first().focus().select();
            translation.highlight();
        });
    },

    'saveForm': function() {
        var self = this,
            values = {};

        $.when(this.promise).then(function() {
            $.each(self.rows.find('input'), function(k, row) {
                values[row.getAttribute('data-locale')] = row.value;
            });

            self.translationEdit.save(values).then(function() {
                var text = values[self.translationEdit.currentLocale],
                    $translationListItem = $('.translation_list li[data-translation-key="' + self.translationEdit.key + '"]');

                $('mark.inline_translation[data-translation-key="' + self.translationEdit.key + '"]').text(text);
                $translationListItem.find('span').text(text);

                self.closeForm();
            });
        });
    },

    'closeForm': function() {
        var self = this;

        $.when(this.promise).then(function() {
            $translationListItem = $('.translation_list li[data-translation-key="' + self.translationEdit.key + '"]');

            self.translationEdit.highlight(false);
            self.el.removeClass('edit');

            self.translationEdit = null;

            $translationListItem.on('mouseleave', function() {
                var translation = self.translations[$translationListItem.data('translation-key')];

                if (translation) {
                    translation.highlight(false);
                }
            });
        });
    },
};
