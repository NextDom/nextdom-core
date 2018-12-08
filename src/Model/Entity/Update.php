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

use Doctrine\ORM\Mapping as ORM;

/**
 * Update
 *
 * @ORM\Table(name="update", indexes={@ORM\Index(name="status", columns={"status"})})
 * @ORM\Entity
 */
class Update
{

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=127, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=127, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="logicalId", type="string", length=127, nullable=true)
     */
    private $logicalid;

    /**
     * @var string
     *
     * @ORM\Column(name="localVersion", type="string", length=127, nullable=true)
     */
    private $localversion;

    /**
     * @var string
     *
     * @ORM\Column(name="remoteVersion", type="string", length=127, nullable=true)
     */
    private $remoteversion;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=127, nullable=true)
     */
    private $source = 'market';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=127, nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text", length=65535, nullable=true)
     */
    private $configuration;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLogicalid()
    {
        return $this->logicalid;
    }

    public function getLocalversion()
    {
        return $this->localversion;
    }

    public function getRemoteversion()
    {
        return $this->remoteversion;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLogicalid($logicalid)
    {
        $this->logicalid = $logicalid;
        return $this;
    }

    public function setLocalversion($localversion)
    {
        $this->localversion = $localversion;
        return $this;
    }

    public function setRemoteversion($remoteversion)
    {
        $this->remoteversion = $remoteversion;
        return $this;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
