#!/bin/bash
# This file is part of NextDom Software.
#
# NextDom is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# NextDom Software is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NextDom Software. If not, see <http://www.gnu.org/licenses/>.

sudo apt-get install php-xdebug

# /etc/php/7.0/mods-available/xdebug.ini check with php -v | grep PHP

echo xdebug.remote_enable=on >> /etc/php/7.0/mods-available/xdebug.ini
echo xdebug.remote_connect_back=on >> /etc/php/7.0/mods-available/xdebug.ini
echo xdebug.remote_port=9900 >> /etc/php/7.0/mods-available/xdebug.ini

# XDEBUG_SESSION=PHPSTORM (in url or cookie)
# => Firefox : https://addons.mozilla.org/fr/firefox/addon/xdebug-helper-for-firefox/
# => Chromium/Chrome : https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc