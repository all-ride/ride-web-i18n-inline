<?php

namespace ride\web\listener;

use ride\application\orm\OrmManager;

use ride\library\dependency\Dependency;
use ride\library\dependency\DependencyCall;
use ride\library\dependency\DependencyCallArgument;
use ride\library\dependency\DependencyContainer;
use ride\library\dependency\DependencyInjector;
use ride\library\event\Event;
use ride\library\http\Request;
use ride\library\security\SecurityManager;

use ride\web\WebApplication;
use ride\web\base\menu\Menu;
use ride\web\base\menu\MenuItem;
use ride\web\mvc\view\TemplateView;

/**
 * ApplicationListener
 */
class I18nApplicationListener {

    /**
     * Load the module CSS and JS
     *
     * @param Event $event
     */
    public function loadScripts(Event $event, Request $request) {
        $view = $event->getArgument('web')->getResponse()->getView();

        if (!($view instanceof TemplateView)) {
            return;
        }

        // Add translator CSS and JavaScript
        $view->addStyle($request->getBaseUrl().'/css/inline-translator.css');
        $view->addJavascript($request->getBaseUrl().'/js/inline-translator.js');
    }

    /**
     * Add the menu item in the backend
     *
     * @param Event $event The event
     * @param Request $request The request
     * @param SecurityManager $securityManager The securityManager
     */
    public function loadMenu(Event $event, Request $request, SecurityManager $securityManager, WebApplication $web) {
        // Check for the /l10n** permission on the logged in user
        $user = $securityManager->getUser();
        if (!$user || !$securityManager->isPathAllowed('/l10n**')) {
            return;
        }

        // Check the current translator preference
        $toggle = $user->getPreference('translator') ? "disable" : "enable";
        $referer = '?referer=' . urlencode($request->getUrl());

        // Create the URL
        $url = $web->getUrl('l10n.api.translator.toggle', array()) . $referer;

        // Create the menu item
        $menuItem = new MenuItem();
        $menuItem->setTranslation('translator.toggle.' . $toggle);
        $menuItem->setUrl($url);
        $menuItem->setWeight(100);
        $taskbar = $event->getArgument('taskbar');
        $applicationMenu = $taskbar->getApplicationsMenu();
        $applicationMenu->addMenuItem($menuItem);
    }
}
