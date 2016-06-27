<?php

namespace ride\web\api\controller;

use ride\library\i18n\I18n;
use ride\library\orm\OrmManager;

use ride\web\base\controller\AbstractController;

/**
 * I18nRestController
 *
 * This controller exposes API calls to manage inline translations
 */
class I18nApiController extends AbstractController {

    /**
     * The i18n facade
     *
     * @var I18n
     */
    private $i18n;

    /**
     * The OrmManager
     *
     * @var OrmManager
     */
    private $orm;

    /**
     * Inject the needed dependencies
     *
     * @param I18n $i18n
     * @param OrmManager $orm
     */
    public function __construct(I18n $i18n, OrmManager $orm) {
        $this->i18n = $i18n;
        $this->orm = $orm;
    }

    /**
     * Generate an inline translator popup based on a translation key
     *
     * @param string $key The translation key
     *
     * @return View
     */
    public function getTranslation($key) {
        $translations = array();
        $locales = $this->i18n->getLocales();

        foreach($locales as $locale=>$value) {
            $translations[$locale] = array(
                'key' => $key,
                'code' => $locale,
                'locale' => $this->i18n->getLocale($locale)->getName(),
                'translation' => $this->i18n->getTranslator($locale)->getTranslation($key)
            );
        }

        $this->setJsonView($translations);
    }

    /**
     * Post a translated key
     *
     * @param string $key The translation key
     *
     * @return JsonView
     */
    public function postTranslation($locale, $key) {
        $translations = $this->request->getBodyParameter('translations');

        foreach($translations as $translationLocale => $translation) {
            if (!$translation) {
                continue;
            }

            $this->i18n->getTranslator($translationLocale)->setTranslation($key, $translation);
        }

        $this->setJsonView(
            array('translation' => $this->i18n->getTranslator($locale)->getTranslation($key))
        );
    }

    /**
     * Toggle the translator for the current user
     */
    public function toggleTranslator() {
        $userModel = $this->orm->getUserModel();
        $user = $this->getUser();
        $toggle = !$user->getPreference('translator');

        $user->setPreference('translator', $toggle);
        $userModel->save($user);

        $redirect = explode("?", $this->request->getQueryParameter('referer'));
        $redirect = $redirect[0];
        $this->response->setRedirect($redirect);
    }

}
