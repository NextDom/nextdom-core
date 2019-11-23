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

use NextDom\Enums\DateFormat;
use NextDom\Exceptions\CoreException;
use NextDom\Helpers\DBHelper;
use NextDom\Helpers\NextDomHelper;
use NextDom\Helpers\Utils;
use NextDom\Managers\ConfigManager;
use NextDom\Managers\UserManager;
use PragmaRX\Google2FA\Google2FA;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User implements EntityInterface
{

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=45, nullable=true)
     */
    protected $login;

    /**
     * @var string
     *
     * @ORM\Column(name="profils", type="string", length=45, nullable=false)
     */
    protected $profils = 'admin';

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    protected $password;

    /**
     * @var string
     *
     * @ORM\Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    protected $hash;

    /**
     * @var string
     *
     * @ORM\Column(name="rights", type="text", length=65535, nullable=true)
     */
    protected $rights;

    /**
     * @var integer
     *
     * @ORM\Column(name="enable", type="integer", nullable=true)
     */
    protected $enable = 1;
    protected $_changed = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    public function preInsert()
    {
        if (is_object(UserManager::byLogin($this->getLogin()))) {
            throw new CoreException(__('Ce nom d\'utilisateur est déja pris'));
        }
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @param $_login
     * @return $this
     */
    public function setLogin($_login)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->login, $_login);
        $this->login = $_login;
        return $this;
    }

    /**
     * Method called before save in database
     */
    public function preSave()
    {
        if ($this->getLogin() == '') {
            throw new CoreException(__('Le nom d\'utilisateur ne peut pas être vide'));
        }
        $admins = UserManager::byProfils('admin', true);
        if (count($admins) == 1 && $this->getProfils() == 'admin' && !$this->isEnabled()) {
            throw new CoreException(__('Vous ne pouvez désactiver le dernier utilisateur'));
        }
        if (count($admins) == 1 && $admins[0]->getId() == $this->getid() && $this->getProfils() != 'admin') {
            throw new CoreException(__('Vous ne pouvez changer le profil du dernier administrateur'));
        }
    }

    /**
     * @return string
     */
    public function getProfils()
    {
        return $this->profils;
    }

    /**
     * @param $_profils
     * @return $this
     */
    public function setProfils($_profils)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->profils, $_profils);
        $this->profils = $_profils;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enable != 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $_id
     * @return $this
     */
    public function setId($_id)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->id, $_id);
        $this->id = $_id;
        return $this;
    }

    /*     * **********************Getteur Setteur*************************** */

    /**
     * @return int
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * @param $_enable
     * @return $this
     */
    public function setEnable($_enable)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->enable, $_enable);
        $this->enable = $_enable;
        return $this;
    }

    public function preRemove()
    {
        if (count(UserManager::byProfils('admin', true)) == 1 && $this->getProfils() == 'admin') {
            throw new CoreException(__('Vous ne pouvez supprimer le dernier administrateur'));
        }
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function remove()
    {
        NextDomHelper::addRemoveHistory(['id' => $this->getId(), 'name' => $this->getLogin(), 'date' => date(DateFormat::FULL), 'type' => 'user']);
        return DBHelper::remove($this);
    }

    public function refresh()
    {
        DBHelper::refresh($this);
    }

    /**
     * @deprecated
     * @return boolean vrai si l'utilisateur est valide
     */
    public function is_Connected()
    {
        return $this->isConnected();
    }

    /**
     *
     * @return boolean vrai si l'utilisateur est valide
     */
    public function isConnected()
    {
        return (is_numeric($this->id) && $this->login != '');
    }

    /**
     * @param $_code
     * @return bool
     */
    public function validateTwoFactorCode($_code)
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->getOptions('twoFactorAuthentificationSecret'), $_code);
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getOptions($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->options, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setOptions($_key, $_value)
    {
        $options = Utils::setJsonAttr($this->options, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->options, $options);
        $this->options = $options;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $_password
     * @return $this
     */
    public function setPassword($_password)
    {
        $_password = (!Utils::isSha512($_password)) ? Utils::sha512($_password) : $_password;
        $this->_changed = Utils::attrChanged($this->_changed, $this->password, $_password);
        $this->password = $_password;
        return $this;
    }

    /**
     * @param string $_key
     * @param string $_default
     * @return array|bool|mixed|null|string
     */
    public function getRights($_key = '', $_default = '')
    {
        return Utils::getJsonAttr($this->rights, $_key, $_default);
    }

    /**
     * @param $_key
     * @param $_value
     * @return $this
     */
    public function setRights($_key, $_value)
    {
        $rights = Utils::setJsonAttr($this->rights, $_key, $_value);
        $this->_changed = Utils::attrChanged($this->_changed, $this->rights, $rights);
        $this->rights = $rights;
        return $this;
    }

    /**
     * @return string
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function getHash()
    {
        if ($this->hash == '' && $this->id != '') {
            $hash = ConfigManager::genKey();
            while (is_object(UserManager::byHash($hash))) {
                $hash = ConfigManager::genKey();
            }
            $this->setHash($hash);
            $this->save();
        }
        return $this->hash;
    }

    /**
     * @param $_hash
     * @return $this
     */
    public function setHash($_hash)
    {
        $this->_changed = Utils::attrChanged($this->_changed, $this->hash, $_hash);
        $this->hash = $_hash;
        return $this;
    }

    /**
     * @return bool
     * @throws \NextDom\Exceptions\CoreException
     * @throws \ReflectionException
     */
    public function save()
    {
        return DBHelper::save($this);
    }

    /**
     * @return bool
     */
    public function getChanged()
    {
        return $this->_changed;
    }

    /**
     * @param $_changed
     * @return $this
     */
    public function setChanged($_changed)
    {
        $this->_changed = $_changed;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return 'user';
    }
}
