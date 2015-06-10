<?php

namespace ride\library\i18n\translator;

use ride\library\i18n\translator\AdminTranslator;
use ride\library\i18n\translator\GenericTranslatorManager;
use \ride\library\i18n\translator\GenericTranslator;

use ride\library\i18n\locale\Locale;
use ride\library\i18n\translator\io\TranslationIO;
use ride\library\security\SecurityManager;

/**
 * Manager of the translators
 */
class AdminTranslatorManager extends GenericTranslatorManager {

    /**
     * {@inheritdoc}
     */
    protected function createTranslator(Locale $locale) {
        return new AdminTranslator($locale, $this->io);
    }

}
