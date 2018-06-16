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

namespace NextDom\Helpers;

/**
 * Classe temporaire pour stocker différents états.
 */
class Status
{
    /**
     * @var bool Statut de la connexion de l'utilisateur
     */
    private static $connectState = false;

    /**
     * @var bool Statut de la connexion de l'utilisateur en administrateur
     */
    private static $connectAdminState = false;

    /**
     * @var bool Statut du mode récupération
     */
    private static $rescueMode = false;

    /**
     * Initialiser le statut du mode récupération
     */
    public static function initRescueModeState()
    {
        if (\init('rescue', 0) == 1) {
            self::$rescueMode = true;
        }
    }

    /**
     * Initialiser le statut de la connexion de l'utilisateur
     */
    public static function initConnectState()
    {
        self::$connectState = \isConnect();
        self::$connectAdminState = \isConnect('admin');
    }

    /**
     * Obtenir le statut de la connexion de l'utilisateur
     * @return bool Statut de la connexion de l'utilisateur
     */
    public static function isConnect()
    {
        return self::$connectState;
    }

    /**
     * Test si l'utilisateur est connecté et lève une exception si ce n'est pas le cas.
     *
     * @throws \Exception
     */
    public static function isConnectedOrFail()
    {
        if (!self::$connectState) {
            throw new \Exception(__('401 - Accès non autorisé', 'system'));
        }
    }

    /**
     * Test si l'utilisateur est connecté avec les droits admin et lève une exception si ce n'est pas le cas.
     *
     * @throws \Exception
     */
    public static function isConnectedAdminOrFail()
    {
        if (!self::$connectAdminState) {
            throw new \Exception(__('401 - Accès non autorisé', 'system'));
        }
    }

    /**
     * Obtenir le statut de la connexion de l'utilisateur en administrateur
     * @return bool Statut de la connexion de l'utilisateur en administrateur
     */
    public static function isConnectAdmin()
    {
        return self::$connectAdminState;
    }

    /**
     * Obtenir le statut du mode récupération
     * @return bool Statut du mode récupération
     */
    public static function isRecueMode()
    {
        return self::$rescueMode;
    }
}
