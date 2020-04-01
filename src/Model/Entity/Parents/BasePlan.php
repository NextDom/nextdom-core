<?php

namespace NextDom\Model\Entity\Parents;

use NextDom\Helpers\Utils;

abstract class BasePlan extends BaseEntity
{
    use ConfigurationEntity, DisplayEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="link_type", type="string", length=127, nullable=true)
     */
    protected $link_type;

    /**
     * @var integer
     *
     * @ORM\Column(name="link_id", type="integer", nullable=true)
     */
    protected $link_id;

    /**
     * @var string
     *
     * @ORM\Column(name="position", type="text", length=65535, nullable=true)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", length=65535, nullable=true)
     */
    protected $css;

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getPosition($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->position, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setPosition($_key, $_value)
    {
        $position = Utils::setJsonAttr($this->position, $_key, $_value);
        $this->updateChangeState($this->position, $position);
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink_type()
    {
        return $this->link_type;
    }

    /**
     * @param $_link_type
     * @return $this
     */
    public function setLink_type($_link_type)
    {
        $this->updateChangeState($this->link_type, $_link_type);
        $this->link_type = $_link_type;
        return $this;
    }

    /**
     * @return int
     */
    public function getLink_id()
    {
        return $this->link_id;
    }

    /**
     * @param $_link_id
     * @return $this
     */
    public function setLink_id($_link_id)
    {
        $this->updateChangeState($this->link_id, $_link_id);
        $this->link_id = $_link_id;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getCss($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->css, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setCss($_key, $_value)
    {
        $css = Utils::setJsonAttr($this->css, $_key, $_value);
        $this->updateChangeState($this->css, $css);
        $this->css = $css;
        return $this;
    }

    abstract public function preInsert();

    abstract public function preSave();

    abstract public function getHtml();

    abstract public function getLink();
}