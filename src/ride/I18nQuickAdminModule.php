<?php

namespace ride;

use ride\library\security\SecurityManager;
use ride\library\event\Event;
use ride\library\dependency\Dependency,
    ride\library\dependency\DependencyCall,
    ride\library\dependency\DependencyCallArgument,
    ride\library\dependency\DependencyContainer,
    ride\library\dependency\DependencyInjector;
use ride\web\mvc\view\TemplateView;

/**
 * @Class I18nQuickAdminModule
 */
class I18nQuickAdminModule {

    /**
     * @param Event $event
     * @param SecurityManager $securityManager
     * @param DependencyInjector $dependencyInjector
     *
     * Initialise the module
     */
    public function init(Event $event, SecurityManager $securityManager, DependencyInjector $dependencyInjector) {
        if (!$securityManager->isPathAllowed('/l10n**')) {
            return;
        }

        $this->loadTranslator($dependencyInjector);
    }

    /**
     * @param Event $event
     *
     * load the module CSS and JS
     */
    public function loadScripts(Event $event) {
        $view = $event->getArgument('web')->getResponse()->getView();

        if (!($view instanceof TemplateView)) {
            return;
        }
        $view->addStyle('css/admin-translation.css');
        $view->addJavascript('js/admin-translation.js');
    }

    /**
     * @param DependencyInjector $dependencyInjector
     *
     * Set the I18n translator manager to the AdminTranslatorManager
     */
    private function loadTranslator(DependencyInjector $dependencyInjector) {
        // Add translator dependency
        $container = $dependencyInjector->getContainer();
        $translator = new Dependency('ride\library\i18n\translator\AdminTranslatorManager', 'generic');
        $translator->addInterface('ride\library\i18n\translator\TranslatorManager');

        $call = new DependencyCall('__construct');
        $call->addArgument(new DependencyCallArgument(
            'io',
            'dependency',
            array(
                'interface' => 'ride\library\i18n\translator\io\TranslationIO',
                'id' => '%system.l10n.io.default|json%'
            )
        ));

        $translator->addCall($call);
        $container->addDependency($translator);
        $dependencyInjector->setContainer($container);

        $i18n = $dependencyInjector->get('ride\library\i18n\I18n');
        $tm = $dependencyInjector->get('ride\library\i18n\translator\AdminTranslatorManager');
        $i18n->setTranslatorManager($tm);
    }
}
