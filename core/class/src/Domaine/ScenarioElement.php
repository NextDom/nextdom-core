<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 12/06/2018
 * Time: 18:28
 */

namespace NextDom\src\Domaine;


class ScenarioElement
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $order;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $options;

    /**
     * @var string
     */
    private $log;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ScenarioElement
     */
    public function setId(int $id): ScenarioElement
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return ScenarioElement
     */
    public function setOrder(int $order): ScenarioElement
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ScenarioElement
     */
    public function setType($type): ScenarioElement
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ScenarioElement
     */
    public function setName($name): ScenarioElement
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getOptions(): string
    {
        return $this->options;
    }

    /**
     * @param string $options
     * @return ScenarioElement
     */
    public function setOptions($options): ScenarioElement
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getLog(): string
    {
        return $this->log;
    }

    /**
     * @param string $log
     * @return ScenarioElement
     */
    public function setLog($log): ScenarioElement
    {
        $this->log = $log;
        return $this;
    }


}