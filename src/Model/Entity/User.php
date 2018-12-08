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
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
{

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=45, nullable=true)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="profils", type="string", length=45, nullable=false)
     */
    private $profils = 'admin';

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    private $options;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @var string
     *
     * @ORM\Column(name="rights", type="text", length=65535, nullable=true)
     */
    private $rights;

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    private $enable = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getLogin()
    {
        return $this->login;
    }

    public function getProfils()
    {
        return $this->profils;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function getEnable()
    {
        return $this->enable;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLogin($login)
    {
        $this->login = $login;
        return $this;
    }

    public function setProfils($profils)
    {
        $this->profils = $profils;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    public function setRights($rights)
    {
        $this->rights = $rights;
        return $this;
    }

    public function setEnable($enable)
    {
        $this->enable = $enable;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

}
