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

namespace NextDom\Enums;

/**
 * Class JeedomMarketCert
 * @package NextDom\Enums
 */
class JeedomMarketCert extends Enum
{
    const OFFICIAL = 'Officiel';
    const ADVISED = 'Conseillé';
    const LEGACY = 'Legacy';
    const OBSOLETE = 'Obsolète';
    const PREMIUM = 'Premium';
    const PARTNER = 'Partenaire';
}
