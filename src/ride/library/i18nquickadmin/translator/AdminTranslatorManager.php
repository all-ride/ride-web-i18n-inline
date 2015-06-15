<?php

namespace ride\library\i18nquickadmin\translator;

use ride\library\i18nquickadmin\translator\AdminTranslator;
use ride\library\i18n\translator\GenericTranslatorManager;
use \ride\library\i18n\translator\GenericTranslator;

use ride\library\i18n\locale\Locale;
use ride\library\i18n\translator\io\TranslationIO;
use ride\library\dependency\DependencyInjector;

/**
 * Manager of the translators
 */
class AdminTranslatorManager extends GenericTranslatorManager {

    /**
     * @var DependencyInjector $injector
     */
    private $injector;

    /**
     * Constructs a new translation manager
     * @param \ride\library\i18n\translator\io\TranslationIO $io
     * @param \ride\library\dependency\DependencyInjector $injector
     * @return null
     */
    public function __construct(TranslationIO $io, DependencyInjector $injector) {
        $this->injector = $injector;
        parent::__construct($io);
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslator(Locale $locale) {
        $sm = $this->injector->get('ride\library\security\SecurityManager');

        if ($sm->getUser()->getPreference('translator')) {
            return new AdminTranslator($locale, $this->io);
        }

        return new GenericTranslator($locale, $this->io);
    }

}
