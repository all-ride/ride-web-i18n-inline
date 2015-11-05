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
        $keySlug = str_replace('.', '-', $key);
        $translation = '<mark class="inline__translator inline__translator--'.$keySlug.'">'.$translation.'</mark>&nbsp;<mark class="inline__translator inline__translator--toggle icon icon--globe" title="Click to edit ['.$key.']" data-key="'.$key.'" data-locale="'.$this->locale.'" data-for="inline__translator--'.$keySlug.'"></mark>';

        return $translation;
    }
}
