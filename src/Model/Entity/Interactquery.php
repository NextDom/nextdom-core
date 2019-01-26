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

/**
 * Interactquery
 *
 * @ORM\Table(name="interactQuery", indexes={@ORM\Index(name="fk_sarahQuery_sarahDef1_idx", columns={"interactDef_id"}), @ORM\Index(name="query", columns={"query"})})
 * @ORM\Entity
 */
class Interactquery
{

    /**
     * @var integer
     *
     * @ORM\Column(name="interactDef_id", type="integer", nullable=false)
     */
    private $interactdefId;

    /**
     * @var string
     *
     * @ORM\Column(name="query", type="text", length=65535, nullable=true)
     */
    private $query;

    /**
     * @var string
     *
     * @ORM\Column(name="actions", type="text", length=65535, nullable=true)
     */
    private $actions;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getInteractdefId()
    {
        return $this->interactdefId;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getActions()
    {
        return $this->actions;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setInteractdefId($interactdefId)
    {
        $this->interactdefId = $interactdefId;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function setActions($actions)
    {
        $this->actions = $actions;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
