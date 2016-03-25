<?php

namespace ride\library\i18n\translator;

use ride\library\dependency\DependencyInjector;

use ride\library\i18n\locale\Locale;
use ride\library\i18n\translator\GenericTranslator;
use ride\library\i18n\translator\GenericTranslatorManager;
use ride\library\i18n\translator\InlineTranslator;
use ride\library\i18n\translator\io\TranslationIO;

use ride\library\security\SecurityManager;

/**
 * InlineTranslatorManager
 */
class InlineTranslatorManager extends GenericTranslatorManager {

    /**
     * @var DependencyInjector $injector
     */
    private $dependencyInjector;

    /**
     * Constructs a new inline translator manager
     *
     * @param TranslationIO $io
     * @param DependencyInjector $dependencyInjector
     */
    public function __construct(TranslationIO $io, DependencyInjector $dependencyInjector) {
        $this->dependencyInjector = $dependencyInjector;

        return parent::__construct($io);
    }

    /**
     * Get the security manager
     * @return SecurityManager
     */
    protected function getSecurityManager() {
        return $this->dependencyInjector->get('ride\library\security\SecurityManager');
    }

    /**
     * {@inheritdoc}
     */
    protected function createTranslator(Locale $locale) {
        $securityManager = $this->getSecurityManager();

        // If the user is logged in, and its translator preference is enabled, use the InlineTranslator
        if ($securityManager->getUser() && $securityManager->getUser()->getPreference('translator')) {
            return new InlineTranslator($locale, $this->io);
        }

        // Fallback to the generic translator
        return new GenericTranslator($locale, $this->io);
    }

}
