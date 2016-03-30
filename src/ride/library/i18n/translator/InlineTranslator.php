<?php

namespace ride\library\i18n\translator;

use ride\library\i18n\translator\GenericTranslator;

/**
 * InlineTranslator
 *
 * Translator of keys into localized translations
 */
class InlineTranslator extends GenericTranslator {

    /**
     * {@inheritdoc}
     */
    public function translate($key, array $vars = null, $default = null) {
        $translation = parent::translate($key, $vars, $default);

        $translation = "<mark title=\"{$key}\" class=\"inline_translation\" data-translation-key=\"{$key}\" data-locale=\"{$this->locale}\">{$translation}</mark>";

        return $translation;
    }
}
