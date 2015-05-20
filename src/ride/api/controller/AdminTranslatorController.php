<?php

namespace ride\api\controller;

use ride\library\html\Pagination;
use ride\library\i18n\I18n;
use ride\library\orm\OrmManager;
use ride\library\system\file\browser\FileBrowser;
use ride\library\validation\exception\ValidationException;

use ride\web\base\controller\AbstractController;
use ride\web\base\form\AssetComponent;

class AdminTranslatorController extends AbstractController {

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

    public function postTranslations(I18n $i18n) {
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

}
