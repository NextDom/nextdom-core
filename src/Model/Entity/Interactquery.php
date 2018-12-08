<?php

namespace NextDom\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

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
