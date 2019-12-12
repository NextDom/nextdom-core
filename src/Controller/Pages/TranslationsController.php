<?php


namespace NextDom\Controller\Pages;

use NextDom\Controller\BaseController;
use NextDom\Helpers\AjaxHelper;
use NextDom\Helpers\TranslateHelper;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Class TranslationsController
 * @package NextDom\Controller\Pages
 */
class TranslationsController extends BaseController
{
    /**
     * @var Translator Translate tool
     */
    private static $translator = null;

    /**
     * @param $pageData
     * @return string
     * @throws \Exception
     */
    public static function get(&$pageData): string
    {
        $language = TranslateHelper::getLanguage();
        $filename = TranslateHelper::getPathTranslationFile($language);

        self::$translator = new Translator($language, null, NEXTDOM_DATA . '/cache/i18n');
        self::$translator->addLoader('yaml', new YamlFileLoader());
        self::$translator->addResource('yaml', $filename, $language);

        $arrayOftranslations = self::$translator->getCatalogue()->all();

        return \json_encode($arrayOftranslations, true);
    }

}
