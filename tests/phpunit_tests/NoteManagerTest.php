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

use NextDom\Model\Entity\Note;
use NextDom\Managers\NoteManager;

require_once(__DIR__ . '/../../src/core.php');

class NoteManagerTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function setUp()
    {

    }

    public function testByOnNote() {
        $note = NoteManager::byId(1);
        $this->assertEquals(Note::class, get_class($note));
        $this->assertEquals(1, $note->getId());
        $this->assertEquals('Note de test', $note->getName());
    }

    public function testByOnBad() {
        $note = NoteManager::byId(71);
        $this->assertFalse($note);
    }

    public function testAll() {
        $notes = NoteManager::all();
        $this->assertEquals(Note::class, get_class($notes[0]));
        $this->assertEquals(2, $notes[1]->getId());
        $this->assertEquals('Une autre note', $notes[1]->getName());
    }

}