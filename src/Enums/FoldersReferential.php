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

class FoldersReferential extends Enum
{

    const NEXTDOMFOLDERS = ['assets','backup','core','data','docs','install','log','mobile','plugins','public','scripts','src','tests','translations','vendor','views','.github','.sass-cache','.git','.idea'];
    const NEXTDOMFILES = ['cache.tar.gz' ,'composer.json','composer.lock','COPYING','DB_backup.sql','favicon.ico','index.php','LICENSE','manifest.json','manifest.webmanifest','mobile.manifest.php','package.json','phpunit.xml.dist','README.md','robots.txt','.travis.yml','.htaccess','.coveralls.yml','.gitignore'];

}