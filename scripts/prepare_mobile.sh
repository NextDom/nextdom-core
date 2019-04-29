#!/bin/sh

ROOT_DIR=$(dirname $(cd $(dirname $0) && pwd -P))
cd $ROOT_DIR/src/mobile
npm install
ln -s $ROOT_DIR/assets/icon $ROOT_DIR/mobile/src/assets/icons
