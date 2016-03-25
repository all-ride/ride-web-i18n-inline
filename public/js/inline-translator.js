
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

/**
 * Opens, closes and save the translator popup
 */
var TranslatorPopup = {

    /**
     * @var {DOMElement} elem The translation element currently active
     */
    elem: null,

    /**
     * @var {array} inputs The input fields of all translations for this translation key
     */
    inputs: [],

    /**
     * Open the popup
     *
     * @param {DOMElement} elem The translation element currently active
     * @param {string} html The html for this popup
     */
    open: function(elem, html) {
        var self = this;
        this.elem = elem;
        $(document.body).prepend($.parseHTML(html));

        // Clicking save saves and closes the popup
        $('#save-translation').on('click', function(e) {
            e.preventDefault();
            self.save();
            self.close();
        });

        // Clicking cancel closes the popup
        $('#cancel-translation').on('click', function(e) {
            e.preventDefault();
            self.close();
        });

        // Clicking the popup prevents the document click event to trigger
        $('.popup').on('click', function(e) {
            e.stopPropagation();
        });

        // Clicking the document closes the popup
        $(document).on('click', function(e) {
            self.close();
        });

        // ESC key closes the popup
        $(document).on('keydown', function(e) {
            if (e.which == 27) {
                self.close();
            }
        });

        // Select the popup input of the current locale
        $('input[data-locale=' + this.elem.data('locale') +']').select();

        // Set all inputs as an object variable, which will be saved on close
        this.inputs = $('#popup-translation input');
    },

    /**
     * Save the data of the popup
     */
    save: function() {
        var data = {},
            elem = this.elem;

        // Create a data array for all locale inputs
        $.each(this.inputs, function(key, input) {
            data[input.name] = input.value;
        });

        // Post the data and set the label with the current translation
        InlineTranslatorAPI.post(elem.data('locale'), elem.data('key'), {'translations' : data}).success(function(response) {
            $("." + elem.data('for')).text(response.translation);
        });
    },

    /**
     * Close the popup by unregistering the events, resetting variables and removing the popup DOM
     */
    close: function() {
        // Disable event listeners
        $('#save-translation').off('click');
        $('#cancel-translation').off('click');
        $('.popup').off('click');
        $(document).off('keydown');

        // Reset variables
        this.inputs = [];
        this.elem = null;

        // Remove the popup DOM
        $('#popup-translation').remove();
    },
};

$(document).ready(function() {
    // Initialize the translator
    InlineTranslatorAPI.init('/api/v1/i18n');

    // Add click events to all translateable elements
    $('mark.inline__translator--toggle').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var elem = $(this);

        // Get the translation popup for this element's key
        InlineTranslatorAPI.get(elem.data('key')).success(function(html) {
            TranslatorPopup.open(elem, html);
        });
    });
});
