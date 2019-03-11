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

define('ROOT_PATH', realpath(__DIR__ . '/../..'));
define('TRANSLATIONS_PATH', ROOT_PATH . '/translations');
define('CACHE_PATH', ROOT_PATH . '/var/cache/i18n');

require (ROOT_PATH . '/vendor/autoload.php');

class I18nTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        system('mkdir -p ' . ROOT_PATH . '/var/cache/i18n');
    }

    public static function tearDownAfterClass()
    {

    }

    public function setUp()
    {
        shell_exec('rm -fr '.CACHE_PATH.'/*');
    }

    private function getTranslator($locale)
    {
        $translator = new Translator($locale, null, CACHE_PATH);
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', TRANSLATIONS_PATH . '/' . $locale . '.yml', $locale);
        return $translator;
    }

    public function testNotFoundTranslation()
    {
        $sourceStr = 'An impossible sentence';
        $translator = $this->getTranslator('fr_FR');
        $translatedStr = $translator->trans($sourceStr);
        $this->assertEquals($translatedStr, $sourceStr);
        $this->assertNotEquals(0, count(glob(CACHE_PATH . '/catalogue.fr_FR.*')));
    }

    public function testNormalTranslation()
    {
        $sourceStr = 'connection.password-placeholder';
        $translator = $this->getTranslator('fr_FR');
        $translatedStr = $translator->trans($sourceStr);
        $this->assertEquals('Mot de passe...', $translatedStr);
        $this->assertNotEquals(0, count(glob(CACHE_PATH . '/catalogue.fr_FR.*')));
    }

    public function testNormalFrenchTranslation()
    {
        $sourceStr = 'core.error-401';
        $translator = $this->getTranslator('fr_FR');
        $translatedStr = $translator->trans($sourceStr);
        $this->assertEquals('401 - Accès non autorisé', $translatedStr);
        $this->assertNotEquals(0, count(glob(CACHE_PATH . '/catalogue.fr_FR.*')));
    }

    public function testNormalEnglishTranslation()
    {
        $sourceStr = 'core.error-401';
        $translator = $this->getTranslator('en_US');
        $translatedStr = $translator->trans($sourceStr);
        $this->assertEquals('401 - Unauthorized access', $translatedStr);
        $this->assertNotEquals(0, count(glob(CACHE_PATH . '/catalogue.en_US.*')));
    }

}