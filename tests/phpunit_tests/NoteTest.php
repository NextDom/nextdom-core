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
use NextDom\Managers\NoteManager;
use NextDom\Model\Entity\Note;

require_once(__DIR__ . '/../../src/core.php');

class NoteTest extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
        DBHelper::Prepare('DELETE FROM ' . NoteManager::DB_CLASS_NAME. ' WHERE id > 2', []);
    }

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
        DBHelper::Prepare('DELETE FROM ' . NoteManager::DB_CLASS_NAME. ' WHERE id > 2', []);
    }

    public function testGettersAndSetters()
    {
        // Add in db
        $note = new Note();
        $note->setName('Test note');
        $note->setText('Test note content');

        $this->assertTrue($note->getChanged());
        $note->save();
        $noteId = $note->getId();
        $this->assertFalse($note->getChanged());

        // Test la note add
        $savedNote = NoteManager::byId($noteId);
        $this->assertEquals($noteId, $savedNote->getId());
        $this->assertEquals(Note::class, get_class($savedNote));
        $this->assertEquals('Test note', $savedNote->getName());
        $this->assertEquals('Test note content', $savedNote->getText());
        $this->assertFalse($note->getChanged());
    }

    public function testNoteWithoutTitleError()
    {
        $note = new Note();
        $note->setName('');
        $note->setText('Test note content');
        try {
            $note->save();
            $this->assertTrue(false);
        }
        catch (CoreException $e) {
            $this->assertEquals('Le nom de la note ne peut être vide', $e->getMessage());
        }
    }

    public function testRemove()
    {
        $note = new Note();
        $note->setName('Test note');
        $note->setText('Test note content');
        $this->assertTrue($note->getChanged());
        $note->save();
        $noteId = DBHelper::getLastInsertId();
        $this->assertFalse($note->getChanged());

        $savedNote = NoteManager::byId($noteId);
        $this->assertEquals(Note::class, get_class($savedNote));
        $savedNote->remove();

        $removedNote = NoteManager::byId($noteId);
        $this->assertFalse($removedNote);
    }
}