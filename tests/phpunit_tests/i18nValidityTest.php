<?php
/* This file is part of NextDom.
 *
 * NextDom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NextDom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NextDom. If not, see <http://www.gnu.org/licenses/>.
 */

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class i18nValidityTest extends PHPUnit_Framework_TestCase
{
    public function testsLanguages()
    {
        $languagesToTest = [
            'en_US',
            'fr_FR'
        ];
        foreach ($languagesToTest as $language) {
            $translator = new Translator($language);
            $translator->addLoader('yaml', new YamlFileLoader());
            $translator->addResource('yaml', __DIR__ . '/../../translations/'.$language.'.yml', $language);
            $this->assertTrue(is_string($translator->trans('connection.connect')));
        }
    }
}