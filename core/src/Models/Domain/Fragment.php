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

namespace NextDom\Models\Domain;


class Fragment
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $type = 'plugin';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $logicalId;

    /**
     * @var string
     */
    protected $localVersion;

    /**
     * @var string
     */
    protected $remoteVersion;

    /**
     * @var string
     */
    protected $source = 'market';

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $configuration;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Fragment
     */
    public function setId(int $id): Fragment
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
     * @return Fragment
     */
    public function setType($type): Fragment
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
     * @return Fragment
     */
    public function setName($name): Fragment
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
     * @return Fragment
     */
    public function setLogicalId($logicalId): Fragment
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
     * @return Fragment
     */
    public function setLocalVersion($localVersion): Fragment
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
     * @return Fragment
     */
    public function setRemoteVersion($remoteVersion): Fragment
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
     * @return Fragment
     */
    public function setSource($source): Fragment
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
     * @return Fragment
     */
    public function setStatus($status): Fragment
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
     * @return Fragment
     */
    public function setConfiguration($configuration): Fragment
    {
        $this->configuration = $configuration;
        return $this;
    }


}