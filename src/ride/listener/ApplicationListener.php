<?php

namespace ride\listener;

use ride\library\event\Event;
use ride\web\mvc\view\TemplateView;
use ride\library\mvc\message\MessageContainer;

class ApplicationListener {

    /**
     * Event before the taskbar is processed
     * @var string
     */
    const EVENT_TASKBAR_PRE = 'app.taskbar.pre';

    /**
     * Event after the taskbar is processed
     * @var string
     */
    const EVENT_TASKBAR_POST = 'app.taskbar.post';

    /**
     * Session key to store the response messages
     * @var string
     */
    const SESSION_MESSAGES = 'response.messages';

    /**
     * Handles the response messages. If a redirect is detected, the messages
     * are stored to the session for a next request. If the view is a template
     * view, the messages will be set to the view in the app variable.
     * @param \ride\library\event\Event $event
     * @return null
     */
    public function handleResponseMessages(Event $event) {
        $web = $event->getArgument('web');

        $request = $web->getRequest();
        $response = $web->getResponse();
        $view = $response->getView();

        if (!($view instanceof TemplateView)) {
            return;
        }

        $view->addJavascript('js/admin-translation.js');
        $view->addStyle('css/admin-translation.css');
    }

}
