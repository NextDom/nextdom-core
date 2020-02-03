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

use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\LogHelper;
use NextDom\Managers\InteractDefManager;
use NextDom\Managers\InteractQueryManager;
use NextDom\Managers\PluginManager;
use NextDom\Model\Entity\Plugin;
use NextDom\Model\Entity\InteractDef;
use NextDom\Model\Entity\InteractQuery;

require_once(__DIR__ . '/../../../src/core.php');

class InteractDefTest extends PHPUnit\Framework\TestCase
{
    public static function tearDownAfterClass(): void
    {
        DBHelper::exec('DELETE FROM interactDef WHERE id > 2');
        DBHelper::exec('DELETE FROM interactQuery WHERE id > 7');
    }

    public function tearDown(): void
    {
        DBHelper::exec('DELETE FROM interactDef WHERE id > 2');
        DBHelper::exec('DELETE FROM interactQuery WHERE id > 7');
    }

    public function testCreate()
    {
        $testObj = new InteractDef();
        $testObj->setName('Created interact_def');
        $testObj->setEnable(1);
        $testObj->setGroup('Created group');
        $testObj->setQuery('simple');
        $testObj->save();
        /** @var InteractDef $createdObj */
        $createdObj = InteractDefManager::byId($testObj->getId());
        // Test default reply
        $this->assertEquals('Created interact_def', $createdObj->getName());
        $this->assertEquals('Created group', $createdObj->getGroup());
        // Test default values
        $this->assertEquals('#valeur#', $createdObj->getReply());
        $this->assertEquals('1', $createdObj->getOptions('allowSyntaxCheck'));
        $this->assertEquals('all', $createdObj->getFilters()['eqLogic_id']);
        $interactQueries = InteractQueryManager::byInteractDefId($createdObj->getId());
        $this->assertCount(1, $interactQueries);
        $this->assertEquals('simple', $interactQueries[0]->getQuery());
    }

    public function testQueryVariationCreation()
    {
        $testObj = new InteractDef();
        $testObj->setName('Created variations');
        $testObj->setEnable(1);
        $testObj->setQuery('an example');
        $testObj->setOptions('synonymes', 'an=un,le|example=home');
        $testObj->save();
        /** @var InteractDef $createdObj */
        $createdObj = InteractDefManager::byId($testObj->getId());
        // Test default reply
        $interactQueries = InteractQueryManager::byInteractDefId($createdObj->getId());
        $this->assertCount(5, $interactQueries);
        $this->assertEquals('an example', $interactQueries[0]->getQuery());
    }
}