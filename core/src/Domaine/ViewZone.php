<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 12/06/2018
 * Time: 19:42
 */

namespace NextDom\src\Domaine;


class ViewZone
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $viewId;

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
    private $position;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ViewZone
     */
    public function setId(int $id): ViewZone
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewId(): int
    {
        return $this->viewId;
    }

    /**
     * @param int $viewId
     * @return ViewZone
     */
    public function setViewId(int $viewId): ViewZone
    {
        $this->viewId = $viewId;
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
     * @return ViewZone
     */
    public function setType($type): ViewZone
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
     * @return ViewZone
     */
    public function setName($name): ViewZone
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     * @return ViewZone
     */
    public function setPosition($position): ViewZone
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     * @return ViewZone
     */
    public function setConfiguration($configuration): ViewZone
    {
        $this->configuration = $configuration;
        return $this;
    }



}