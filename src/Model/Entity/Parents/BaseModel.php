<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\DBHelper;

abstract class BaseModel implements \NextDom\Interfaces\EntityInterface
{
    const TABLE_NAME = '';

    /**
     * @var bool State of changes
     */
    protected $_changed = false;

    /**
     * Get changed state
     *
     * @return bool True if attribute has changed
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * Set changed state
     *
     * @param bool $changed New changed state
     *
     * @return $this
     */
    public function setChanged($changed)
    {
        $this->_changed = $changed;
        return $this;
    }

    /**
     * Update change state if values are different
     *
     * @param $oldValue
     * @param $newValue
     */
    public function updateChangeState($oldValue, $newValue)
    {
        if (!$this->_changed) {
            if (is_array($oldValue)) {
                $oldValue = json_encode($oldValue);
            }
            if (is_array($newValue)) {
                $newValue = json_encode($newValue);
            }
            $this->_changed = $oldValue !== $newValue;
        }
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return static::TABLE_NAME;
    }

    /**
     * Save the entity in the database
     */
    public function save()
    {
        DBHelper::save($this);
        return $this;
    }

    /**
     * Remove the entity from the database
     */
    public function remove()
    {
        return DBHelper::remove($this);
    }
}