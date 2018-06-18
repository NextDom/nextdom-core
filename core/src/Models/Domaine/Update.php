<?php
/* This file is part of NextDom.
 *
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


class Update
{
    /**
     * @var int
     */
    private $id;

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
    private $logicalId;

    /**
     * @var string
     */
    private $localVersion;

    /**
     * @var string
     */
    private $remoteVersion;

    /**
     * @var string
     */
    private $source = 'market';

    /**
     * @var string
     */
    private $status;

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
     * @return Update
     */
    public function setId(int $id): Update
    {
        $this->id = $id;
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
     * @return Update
     */
    public function setType($type): Update
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
     * @return Update
     */
    public function setName($name): Update
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogicalId(): string
    {
        return $this->logicalId;
    }

    /**
     * @param string $logicalId
     * @return Update
     */
    public function setLogicalId($logicalId): Update
    {
        $this->logicalId = $logicalId;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalVersion(): string
    {
        return $this->localVersion;
    }

    /**
     * @param string $localVersion
     * @return Update
     */
    public function setLocalVersion($localVersion): Update
    {
        $this->localVersion = $localVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteVersion(): string
    {
        return $this->remoteVersion;
    }

    /**
     * @param string $remoteVersion
     * @return Update
     */
    public function setRemoteVersion($remoteVersion): Update
    {
        $this->remoteVersion = $remoteVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     * @return Update
     */
    public function setSource($source): Update
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Update
     */
    public function setStatus($status): Update
    {
        $this->status = $status;
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
     * @return Update
     */
    public function setConfiguration($configuration): Update
    {
        $this->configuration = $configuration;
        return $this;
    }


}