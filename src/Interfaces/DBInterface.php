<?php

/*
 * This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
 * Copyright (c) 2018 NextDom -- ColonelMoutarde.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace NextDom\Interfaces;

/**
 * Interface DBInterface
 * @package NextDom\Interfaces
 */
interface DBInterface
{
    /**
     * @return bool
     */
    function isDataTableExists(): bool;

    function createDataTable();

    function dropDataTable();

    /**
     * @param string $code
     * @return mixed
     */
    function deleteData(string $code);

    /**
     * @param string $code
     * @return bool
     */
    function isDataExists(string $code): bool;

}
