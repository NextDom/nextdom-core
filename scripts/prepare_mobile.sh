#!/bin/sh

ROOT_DIR=$(dirname $(cd $(dirname $0) && pwd -P))

cd $ROOT_DIR/src/mobile
ln -s $ROOT_DIR/assets/icon $ROOT_DIR/src/mobile/src/assets/icons
npm install
npm run build
rm -fr $ROOT_DIR/mobile
mkdir $ROOT_DIR/mobile
mv $ROOT_DIR/src/mobile/dist/* $ROOT_DIR/mobile
# Reset routes cache
rm -fr /var/lib/nextdom/cache/routes/*