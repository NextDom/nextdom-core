<?php

namespace NextDom\Model\DataClass;


class SystemHealth
{
    public $icon;
    public $nameCode;
    public $state;
    public $result;
    public $comment;
    public $key;

    public function __construct($icon, $nameCode, $state, $result, $comment, $key)
    {
        $this->icon = $icon;
        $this->nameCode = $nameCode;
        $this->state = $state;
        $this->result = $result;
        $this->comment = $comment;
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Get health name
     * @return string
     * @throws \Exception
     */
    public function getName()
    {
        return __($this->nameCode);
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return SystemHealth
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     * @return SystemHealth
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     * @return SystemHealth
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }
}