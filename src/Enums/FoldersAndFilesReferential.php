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

class FoldersAndFilesReferential extends Enum
{

    const NEXTDOM_ROOT_FOLDERS = [NextDomFolder::API, NextDomFolder::ASSETS, NextDomFolder::BACKUP, NextDomFolder::CORE, NextDomFolder::DATA, NextDomFolder::DOCS, NextDomFolder::INSTALL, NextDomFolder::LOG, NextDomFolder::MOBILE, NextDomFolder::NODE_MODULES, NextDomFolder::PLUGINS, NextDomFolder::PUBLIC, NextDomFolder::SCRIPTS, NextDomFolder::SRC, NextDomFolder::TESTS, NextDomFolder::TMP, NextDomFolder::TRANSLATIONS, NextDomFolder::VENDOR, NextDomFolder::VIEWS, NextDomFolder::VAR, NextDomFolder::_GITHUB, NextDomFolder::_SASS_CACHE, NextDomFolder::_GIT, NextDomFolder::_IDEA];
    const NEXTDOM_ROOT_FILES = [NextDomFile::CACHE_TAR_GZ , NextDomFile::CHANGELOG_MD, NextDomFile::COMPOSER_JSON, NextDomFile::COMPOSER_LOCK, NextDomFile::COPYING, NextDomFile::DB_BACKUP_SQL, NextDomFile::INDEX_PHP, NextDomFile::LICENSE, NextDomFile::MANIFEST_JSON, NextDomFile::MANIFEST_WEBMANIFEST, NextDomFile::MOBILE_MANIFEST_PHP, NextDomFile::PACKAGE_JSON, NextDomFile::PACKAGE_LOCK_JSON, NextDomFile::PHPUNIT_XML_dist, NextDomFile::README_MD, NextDomFile::ROBOTS_TXT, NextDomFile::_TRAVIS_YML, NextDomFile::_HTACCESS, NextDomFile::_COVERALLS_YML, NextDomFile::_GITIGNORE, NextDomFile::_SONARCLOUD_PROPERTIES];

    const NEXTDOM_PUBLIC_FOLDERS = ['css', 'icon', 'img', 'js', 'themes'];
    const NEXTDOM_PUBLIC_FILES = ['403.html', '404.html', '500.html', 'here.html'];

    const JEEDOM_BACKUP_FOLDERS = ['3rdparty', 'core', 'data', 'desktop', 'install', 'mobile', 'script', '.github', '.sass-cache', '.git', '.idea'];
    const JEEDOM_BACKUP_FILES = ['cache.tar.gz', 'composer.json', 'composer.lock', 'COPYING', 'Dockerfile', 'DB_backup.sql', 'health.sh', 'here.html', 'index.php', 'index-1.php', 'LICENSE', 'manifest.json', 'manifest.webmanifest', 'mobile.manifest.php', 'README.md', 'robots.txt', 'sick.php', '.htaccess', '.gitignore'];

}
