<?php
/**
 * Created by PhpStorm.
 * User: luc
 * Date: 12/06/2018
 * Time: 18:59
 */

namespace NextDom\src\Domaine;


class User
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $profils;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $options;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $rights;

    /**
     * @var string
     */
    private $enable;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     * @return User
     */
    public function setLogin(string $login): User
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfils(): string
    {
        return $this->profils;
    }

    /**
     * @param string $profils
     * @return User
     */
    public function setProfils(string $profils): User
    {
        $this->profils = $profils;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password): User
    {
        $this->password = $password;
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
     * @return User
     */
    public function setOptions($options): User
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return User
     */
    public function setHash($hash): User
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getRights(): string
    {
        return $this->rights;
    }

    /**
     * @param string $rights
     * @return User
     */
    public function setRights($rights): User
    {
        $this->rights = $rights;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnable(): string
    {
        return $this->enable;
    }

    /**
     * @param string $enable
     * @return User
     */
    public function setEnable($enable): User
    {
        $this->enable = $enable;
        return $this;
    }
}
