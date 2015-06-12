<?php

namespace ride\library\i18nquickadmin\translator;

use ride\library\i18n\translator\GenericTranslator;

/**
 * Translator of keys into localized translations
 */
class AdminTranslator extends GenericTranslator {

    /**
     * {@inheritdoc}
     */
    public function translate($key, array $vars = null, $default = null) {
        $translation = parent::translate($key, $vars, $default);

        $keySlug = str_replace('.', '-', $key);

        $translation = '<span class="admin-translation--'.$keySlug.'">'.$translation.'</span>&nbsp;<span class="admin-translation icon icon--globe" title="Hold alt+click to edit ['.$key.']" data-key="'.$key.'" data-locale="'.$this->locale.'" data-for="admin-translation--'.$keySlug.'"></span>';

        return $translation;
    }
}
