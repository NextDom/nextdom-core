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

use NextDom\Ajax\ScenarioAjax;
use NextDom\Exceptions\CoreException;
use NextDom\Managers\ScenarioExpressionManager;
use NextDom\Model\Entity\Scenario;
use NextDom\Managers\ScenarioManager;
use NextDom\Managers\ScenarioElementManager;
use NextDom\Managers\ScenarioSubElementManager;
use NextDom\Managers\ScenarioExpresionManager;

require_once(__DIR__ . '/../libs/BaseAjaxTest.php');

class ScenarioAjaxTest extends BaseAjaxTest
{
    /** @var ScenarioAjax */
    private $scenarioAjax = null;

    public function setUp(): void
    {
        $this->scenarioAjax = new ScenarioAjax();
    }

    public function tearDown(): void
    {
        $this->cleanGetParams();
    }

    public function testGet()
    {
        $this->connectAsAdmin();
        $_GET['id'] = 1;
        ob_start();
        $this->scenarioAjax->get();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('Test scenario', $jsonResult['result']['name']);
        $this->assertEquals('schedule', $jsonResult['result']['mode']);
        $this->assertCount(1, $jsonResult['result']['elements']);
    }

    public function testGetBadId()
    {
        $this->connectAsAdmin();
        $this->expectException(CoreException::class);
        $_GET['id'] = 3232;
        $this->scenarioAjax->get();
    }

    public function testAll()
    {
        $this->connectAsAdmin();
        ob_start();
        $this->scenarioAjax->all();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertCount(5, $jsonResult['result']);
        $this->assertEquals('Scenario for tests', $jsonResult['result'][0]['group']);
        $this->assertEquals('0', $jsonResult['result'][4]['timeout']);
    }

    public function testToHtml()
    {
        $this->connectAsAdmin();
        $_GET['id'] = 2;
        $_GET['version'] = 'dashboard';
        ob_start();
        $this->scenarioAjax->toHtml();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('<div class="scenario-widget scenario" data-scenario_id="2"', $jsonResult['result']);
    }

    public function testRemove()
    {
        $this->connectAsAdmin();
        $scenario = new Scenario();
        $scenario->setName('Just a test');
        $scenario->save();
        $scenarioId = $scenario->getId();
        $_GET['id'] = $scenarioId;
        ob_start();
        $this->scenarioAjax->remove();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->expectException(CoreException::class);
        $this->scenarioAjax->remove();
    }

    public function testConvertToTemplateBadId()
    {
        $this->connectAsAdmin();
        $this->expectException(CoreException::class);
        $_GET['id'] = 3232;
        $this->scenarioAjax->convertToTemplate();
    }

    public function testConvertToTemplateBadName()
    {
        $this->connectAsAdmin();
        $this->expectException(CoreException::class);
        $_GET['id'] = 2;
        $_GET['template'] = '.json';
        $this->scenarioAjax->convertToTemplate();
    }

    public function testConvertToTemplate()
    {
        $this->connectAsAdmin();
        $_GET['id'] = 2;
        $_GET['template'] = 'test_convert.json';
        ob_start();
        $this->scenarioAjax->convertToTemplate();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertContains('"name": "Empty scenario",', file_get_contents('/var/lib/nextdom/data/scenario/test_convert.json'));
    }

    public function testCreateScenarioFromAjax()
    {
        $this->connectAsAdmin();
        $_GET['scenario'] = '{
                               "id":"6",
                               "name":"AddedWithAjax",
                               "display":{
                                  "name":"",
                                  "icon":""
                               },
                               "description":"",
                               "group":"",
                               "object_id":"",
                               "configuration":{
                                  "timeline::enable":"0",
                                  "logmode":"default",
                                  "allowMultiInstance":"0",
                                  "syncmode":"0"
                               },
                               "isActive":"1",
                               "isVisible":"1",
                               "timeout":"0",
                               "mode":"provoke",
                               "forecast":{
                                  "prevDate":{
                                     "date":""
                                  },
                                  "nextDate":{
                                     "date":""
                                  }
                               },
                               "type":"expert",
                               "elements":[
                                  {
                                     "id":"",
                                     "type":"if",
                                     "subElements":[
                                        {
                                           "id":"",
                                           "scenarioElement_id":"",
                                           "type":"if",
                                           "subtype":"condition",
                                           "options":{
                                              "enable":"1",
                                              "allowRepeatCondition":"0"
                                           },
                                           "expressions":[
                                              {
                                                 "id":"",
                                                 "scenarioSubElement_id":"",
                                                 "type":"condition",
                                                 "expression":"1 == 1"
                                              }
                                           ]
                                        },
                                        {
                                           "id":"",
                                           "scenarioElement_id":"",
                                           "type":"then",
                                           "subtype":"action",
                                           "expressions":[
                                              {
                                                 "id":"",
                                                 "scenarioSubElement_id":"",
                                                 "type":"action",
                                                 "options":{
                                                    "enable":"1",
                                                    "background":"0",
                                                    "message":"Yes"
                                                 },
                                                 "expression":"log"
                                              }
                                           ]
                                        },
                                        {
                                           "id":"",
                                           "scenarioElement_id":"",
                                           "type":"else",
                                           "subtype":"action",
                                           "expressions":[
                                              {
                                                 "id":"",
                                                 "scenarioSubElement_id":"",
                                                 "type":"element",
                                                 "element":{
                                                    "id":"",
                                                    "type":"for",
                                                    "subElements":[
                                                       {
                                                          "id":"",
                                                          "scenarioElement_id":"",
                                                          "type":"for",
                                                          "subtype":"condition",
                                                          "options":{
                                                             "enable":"1"
                                                          },
                                                          "expressions":[
                                                             {
                                                                "id":"",
                                                                "scenarioSubElement_id":"",
                                                                "type":"condition",
                                                                "expression":"10"
                                                             }
                                                          ]
                                                       },
                                                       {
                                                          "id":"",
                                                          "scenarioElement_id":"",
                                                          "type":"do",
                                                          "subtype":"action",
                                                          "expressions":[
                                                             {
                                                                "id":"",
                                                                "scenarioSubElement_id":"",
                                                                "type":"element",
                                                                "element":{
                                                                   "id":"",
                                                                   "type":"action",
                                                                   "subElements":[
                                                                      {
                                                                         "id":"",
                                                                         "scenarioElement_id":"",
                                                                         "type":"action",
                                                                         "subtype":"action",
                                                                         "options":{
                                                                            "enable":"1"
                                                                         },
                                                                         "expressions":[
                                                                            {
                                                                               "id":"",
                                                                               "scenarioSubElement_id":"",
                                                                               "type":"action",
                                                                               "options":{
                                                                                  "enable":"1",
                                                                                  "background":"0",
                                                                                  "message":"No"
                                                                               },
                                                                               "expression":"log"
                                                                            }
                                                                         ]
                                                                      }
                                                                   ]
                                                                }
                                                             }
                                                          ]
                                                       }
                                                    ]
                                                 }
                                              }
                                           ]
                                        }
                                     ]
                                  }
                               ]
                            }';
        ob_start();
        $this->scenarioAjax->save();
        $result = ob_get_clean();
        $jsonResult = json_decode($result, true);
        $this->assertEquals('ok', $jsonResult['state']);
        $this->assertEquals('provoke', $jsonResult['result']['mode']);
        $this->assertEquals('AddedWithAjax', $jsonResult['result']['name']);
        $scenarioElementId = $jsonResult['result']['scenarioElement'][0];
        $this->assertEquals('6', $scenarioElementId);
        $scenarioElementObj = ScenarioElementManager::byId($scenarioElementId);
        $this->assertEquals('if', $scenarioElementObj->getType());
        /** @var \NextDom\Model\Entity\ScenarioSubElement[] $scenarioSubElements */
        $scenarioSubElements = ScenarioSubElementManager::byScenarioElementId($scenarioElementId);
        $this->assertCount(3, $scenarioSubElements);
        $scenarioExpression = ScenarioExpressionManager::byscenarioSubElementId($scenarioSubElements[2]->getScenarioElement_id());
        $this->assertEquals('log', $scenarioExpression[0]->getExpression());
    }
}