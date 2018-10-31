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

currentDir=$PWD
scriptDir=$(dirname -- "$(readlink -f -- "$BASH_SOURCE")")
nextdomDir=$scriptDir/../

cd $scriptDir/phpdox
composer update
cd $scriptDir/

./phpdox/vendor/bin/phpdox -f phpdox/phpdox.xml
./phpdox/vendor/bin/phpmetrics --report-html=../docs/report ../
 
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/index.xhtml 
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/namespaces.xhtml
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/interfaces.xhtml
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/classes.xhtml
if [ ! -e ../docs/index.xhtml ]; then
	ln -s ../docs/html/index.xhtml ../docs/index.xhtml
fi

cd $currentDir
