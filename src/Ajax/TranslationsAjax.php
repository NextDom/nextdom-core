<?php

/* This file is part of NextDom Software.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom Software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.
 *
 * @Support <https://www.nextdom.org>
 * @Email   <admin@nextdom.org>
 * @Authors/Contributors: Sylvaner, Byackee, cyrilphoenix71, ColonelMoutarde, edgd1er, slobberbone, Astral0, DanoneKiD
 */

namespace NextDom\Ajax;

use NextDom\Helpers\TranslateHelper;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Class TranslationsController
 * @package NextDom\Controller\Pages
 */
class TranslationsAjax extends BaseAjax
{
    /**
     * @var Translator Translate tool
     */
    private static $translator = null;

    /**
     * @return false|string
     * @throws \Exception
     */
    public function allTranslations()
    {
        $language = TranslateHelper::getLanguage();
        $filename = TranslateHelper::getPathTranslationFile($language);

        self::$translator = new Translator($language, null, NEXTDOM_DATA . '/cache/i18n');
        self::$translator->addLoader('yaml', new YamlFileLoader());
        self::$translator->addResource('yaml', $filename, $language);

        $arrayOfTranslations = self::$translator->getCatalogue()->all();

        return \json_encode($arrayOfTranslations);
    }
}
