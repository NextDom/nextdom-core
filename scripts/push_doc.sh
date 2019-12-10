#!/bin/sh

git clone https://github.com/NextDom/nextdom-core-doc
cp scripts/phpdox/phpdox.xml .
wget http://phpdox.de/releases/phpdox.phar
php phpdox.phar
cp -fr nextdom-core-doc/.git docs/
cd docs
mv html docs
cd docs
git add *
git add -u .
# Too much files
#git add *.png
#git add *.css
#git add *.js
#git add *.md5
#git add *.map
#git add *.html
#git add search
#sleep 10
#git add -u .
#sleep 10
git commit -m `date '+%Y%m%d'`
#sleep 10
git push "https://$GITHUB_TOKEN@github.com/NextDom/nextdom-core-doc.git" master
#sleep 10
