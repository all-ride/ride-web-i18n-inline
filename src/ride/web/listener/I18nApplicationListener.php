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
    public function loadScripts(Event $event, Request $request, SecurityManager $securityManager) {
        $view = $event->getArgument('web')->getResponse()->getView();

        if (!($view instanceof TemplateView)) {
            return;
        }

        $user = $securityManager->getUser();
        if (!$user || !$securityManager->isPermissionGranted('permission.i18n.inline.translate') || ($user && !$user->getPreference('translator'))) {
            return;
        }

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
        $user = $securityManager->getUser();
        if (!$user || !$securityManager->isPermissionGranted('permission.i18n.inline.translate')) {
            return;
        }

        // Create the URL
        $referer = '?referer=' . urlencode($request->getUrl());
        $url = $web->getUrl('api.i18n.translator.toggle', array()) . $referer;

        // Create the menu item
        $taskbar = $event->getArgument('taskbar');
        $applicationMenu = $taskbar->getApplicationsMenu();
        $menuItem = new MenuItem();
        $toggle = $user->getPreference('translator') ? "disable" : "enable";

        $menuItem->setTranslation('translator.toggle.' . $toggle);
        $menuItem->setUrl($url);
        $menuItem->setWeight(100);

        $applicationMenu->addMenuItem($menuItem);
    }
}
