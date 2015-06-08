<?php

namespace ride\library\i18n\translator;

use ride\library\i18n\translator\GenericTranslator;

/**
 * Translator of keys into localized translations
 */
class AdminTranslator extends GenericTranslator {

    /**
     * {@inheritdoc}
     */
    public function translate($key, array $vars = null, $default = null) {
        if ($default == null) {
            $default = '[' . $key . ']';
        }

        $translation = $this->io->getTranslation($this->locale, $key);

        if (!$translation) {
            $translation = $default;
        }

        $translation .= '&nbsp;<span class="admin-translation icon icon--pencil-square-o" title="Hold alt+click to edit ['.$key.']" data-key="'.$key.'" data-locale="'.$this->locale.'"></span>';

        if ($translation === null || $vars === null) {
            return $translation;
        }

        if ($vars) {
            foreach ($vars as $key => $value) {
                $translation = str_replace('%' . $key . '%', $value, $translation);
            }
        }

        return $translation;
    }
}
