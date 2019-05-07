#!/bin/sh

ROOT_DIR=$(dirname $(cd $(dirname $0) && pwd -P))
cd $ROOT_DIR/src/mobile
ln -s $ROOT_DIR/assets/icon $ROOT_DIR/src/mobile/src/assets/icons
npm install
npm run serve
