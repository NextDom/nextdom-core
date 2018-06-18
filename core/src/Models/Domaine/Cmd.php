<?php
/* This file is part of NextDom.
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

namespace NextDom\src\Models\Domaine;


class Cmd
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $eqLogicId;

    /**
     * @var string
     */
    private $eqType;

    /**
     * @var string
     */
    private $logicalId;

    /**
     * @var string
     */
    private $genericType;

    /**
     * @var int
     */
    private $order;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $isHistorized;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $subType;

    /**
     * @var string
     */
    private $unite;

    /**
     * @var string
     */
    private $display;

    /**
     * @var int
     */
    private $isVisible;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $html;

    /**
     * @var string
     */
    private $alert;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Cmd
     */
    public function setId(int $id): Cmd
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getEqLogicId(): int
    {
        return $this->eqLogicId;
    }

    /**
     * @param int $eqLogicId
     * @return Cmd
     */
    public function setEqLogicId(int $eqLogicId): Cmd
    {
        $this->eqLogicId = $eqLogicId;
        return $this;
    }

    /**
     * @return string
     */
    public function getEqType()
    {
        return $this->eqType;
    }

    /**
     * @param string $eqType
     * @return Cmd
     */
    public function setEqType(string $eqType): Cmd
    {
        $this->eqType = $eqType;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId()
    {
        return $this->logicalId;
    }

    /**
     * @param string $logicalId
     * @return Cmd
     */
    public function setLogicalId(string $logicalId): Cmd
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getGenericType()
    {
        return $this->genericType;
    }

    /**
     * @param string $genericType
     * @return Cmd
     */
    public function setGenericType(string $genericType): Cmd
    {
        $this->genericType = $genericType;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     * @return Cmd
     */
    public function setOrder(int $order): Cmd
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Cmd
     */
    public function setName(string $name): Cmd
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param string $configuration
     * @return Cmd
     */
    public function setConfiguration(string $configuration): Cmd
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Cmd
     */
    public function setTemplate(string $template): Cmd
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getisHistorized(): string
    {
        return $this->isHistorized;
    }

    /**
     * @param string $isHistorized
     * @return Cmd
     */
    public function setIsHistorized(string $isHistorized): Cmd
    {
        $this->isHistorized = $isHistorized;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Cmd
     */
    public function setType(string $type): Cmd
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @param string $subType
     * @return Cmd
     */
    public function setSubType(string $subType): Cmd
    {
        $this->subType = $subType;
        return $this;
    }

    /**
     * @return string
     */
    public function getUnite()
    {
        return $this->unite;
    }

    /**
     * @param string $unite
     * @return Cmd
     */
    public function setUnite(string $unite): Cmd
    {
        $this->unite = $unite;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * @param string $display
     * @return Cmd
     */
    public function setDisplay(string $display): Cmd
    {
        $this->display = $display;
        return $this;
    }

    /**
     * @return int
     */
    public function getisVisible()
    {
        return $this->isVisible;
    }

    /**
     * @param int $isVisible
     * @return Cmd
     */
    public function setIsVisible(int $isVisible): Cmd
    {
        $this->isVisible = $isVisible;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return Cmd
     */
    public function setValue(string $value): Cmd
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return Cmd
     */
    public function setHtml(string $html): Cmd
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlert()
    {
        return $this->alert;
    }

    /**
     * @param string $alert
     * @return Cmd
     */
    public function setAlert(string $alert): Cmd
    {
        $this->alert = $alert;
        return $this;
    }


}