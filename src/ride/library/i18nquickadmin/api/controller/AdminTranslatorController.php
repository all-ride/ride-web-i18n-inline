<?php

namespace ride\library\i18nquickadmin\api\controller;

use ride\library\html\Pagination;
use ride\library\i18n\I18n;
use ride\library\orm\OrmManager;
use ride\library\system\file\browser\FileBrowser;
use ride\library\validation\exception\ValidationException;

use ride\web\base\controller\AbstractController;
use ride\web\base\form\AssetComponent;
use ride\library\security\SecurityManager;

/**
 * AdminTranslatorController
 */
class AdminTranslatorController extends AbstractController {

    /**
     * @param I18n $i18n
     * @return View
     */
    public function getTranslation(I18n $i18n) {
        $translations = array();
        $locales = $i18n->getLocales();
        $key = $this->request->getQueryParameter('key');

        foreach($locales as $locale=>$value) {
            $translations[] = array(
                "key" => $key,
                "code" => $locale,
                "locale" => $i18n->getLocale($locale)->getName(),
                "translation" => $i18n->getTranslator($locale)->getTranslation($key)
            );
        }

        $this->setTemplateView('popup/translationPopup', array('translations' => $translations, 'key' => $key));
    }

    /**
     * @param I18n $i18n
     * @return JSON
     */
    public function postTranslation(I18n $i18n) {
        $key = $this->request->getQueryParameter('key');
        $locale = $this->request->getQueryParameter('locale');
        $translations = $_POST['translations'];

        if (!$key || !$translations) {
            return;
        }

        foreach($translations as $locale=>$translation) {
            $i18n->getTranslator($locale)->setTranslation($key, $translation);
        }

        echo $i18n->getTranslator($locale)->getTranslation($key);
        return;
    }

    /**
     * @param OrmManager $om
     */
    public function toggle(OrmManager $om) {
        $user = $this->getUser();
        $toggle = !$user->getPreference('translator');
        $userModel = $om->getUserModel();

        $user->setPreference('translator', $toggle);
        $userModel->save($user);

        $redirect = explode("?", $this->request->getQueryParameter('referer'));
        $redirect = $redirect[0];
        $this->response->setRedirect($redirect);
    }

}
