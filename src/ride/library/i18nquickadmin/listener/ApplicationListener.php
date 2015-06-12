<?php

namespace ride\library\i18nquickadmin\listener;

use ride\library\security\SecurityManager;
use ride\library\event\Event;
use ride\library\dependency\Dependency,
    ride\library\dependency\DependencyCall,
    ride\library\dependency\DependencyCallArgument,
    ride\library\dependency\DependencyContainer,
    ride\library\dependency\DependencyInjector;
use ride\library\http\Request;
use ride\web\base\menu\MenuItem,
    ride\web\base\menu\Menu;
use ride\web\mvc\view\TemplateView;
use ride\web\WebApplication;
use ride\application\orm\OrmManager;

/**
 * ApplicationListener
 */
class ApplicationListener {

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
     * @param Event $event The event
     * @param Request $request The request
     * @param SecurityManager $securityManager The securityManager
     */
    public function loadMenu(Event $event, Request $request, SecurityManager $securityManager,WebApplication $web) {
        if (!$securityManager->isPathAllowed('/l10n**')) {
            return;
        }

        $taskbar = $event->getArgument('taskbar');
        $applicationMenu = $taskbar->getApplicationsMenu();
        $referer = '?referer=' . urlencode($request->getUrl());
        $user = $securityManager->getUser();
        $toggle = $user->getPreference('translator') ? "disable" : "enable";
        $url = $web->getUrl('l10n.api.translator.toggle', array()) . $referer;

        $menuItem = new MenuItem();
        $menuItem->setTranslation('translator.toggle.' . $toggle);
        $menuItem->setUrl($url);
        $menuItem->setWeight(100);
        $applicationMenu->addMenuItem($menuItem);
    }
}
