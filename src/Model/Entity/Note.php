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
 */

namespace NextDom\Model\Entity;

use NextDom\Enums\NextDomObj;
use NextDom\Exceptions\CoreException;
use NextDom\Model\Entity\Parents\BaseEntity;
use NextDom\Model\Entity\Parents\NameEntity;

/**
 * Note
 *
 * @ORM\Table(name="note")
 * @ORM\Entity
 */
class Note implements EntityInterface
{
    const TABLE_NAME = NextDomObj::NOTE;

    use NameEntity;

    /**
     * Text of the note
     *
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=65535, nullable=true)
     */
    protected $text;

    /**
     * @var bool Data changed state
     */
    private $_changed = false;

    /**
     * Get id of the note
     *
     * @return int Id of the note
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set note id
     *
     * @param int $id Id of the note
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $id);
        $this->id = $id;
        return $this;
    }

    /**
     * Get the text of the note
     *
     * @return string Text of the note
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set note text
     *
     * @param string $newText Text of the note
     *
     * @return $this
     */
    public function setText($newText)
    {
        $this->updateChangeState($this->text, $newText);
        $this->text = $newText;
        return $this;
    }

    /**
     * Get data change status
     *
     * @return bool True if change occurs
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Set change data state
     *
     * @param $newChangedState
     *
     * @return $this
     */
    public function setChanged($newChangedState)
    {
        $this->_changed = $newChangedState;
        return $this;
    }

    /**
     * Throw exception if note doesn't have name
     * @throws CoreException
     */
    public function preSave()
    {
        if (trim($this->getName()) == '') {
            throw new CoreException(__('entity.note.name-cannot-be-empty'));
        }
    }

    /**
     * Save note in database
     *
     * @return Note True on success
     *
     * @throws CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        if ($this->_changed) {
            parent::save();
            $this->_changed = false;
        }
        return $this;
    }
}
