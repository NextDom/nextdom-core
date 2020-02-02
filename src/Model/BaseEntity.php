<?php

namespace NextDom\Model;

use NextDom\Helpers\Utils;

abstract class BaseEntity implements \NextDom\Interfaces\EntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var bool State of changes
     */
    protected $_changed = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

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

    abstract function save();
    abstract function remove();
}