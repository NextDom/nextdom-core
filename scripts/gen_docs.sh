#!/bin/bash

cd phpdox
composer update
cd ..

./phpdox/vendor/bin/phpdox -f phpdox/phpdox.xml
./phpdox/vendor/bin/phpmetrics --report-html=../docs/report ../
 
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/index.xhtml 
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/namespaces.xhtml
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/interfaces.xhtml
sed -i '/<a href="source\/index.xhtml">Source<\/a>/r phpdox/addReport.txt'  ../docs/html/classes.xhtml
if [ ! -e ../docs/index.xhtml ]; then
	ln -s ../docs/html/index.xhtml ../docs/index.xhtml
fi

