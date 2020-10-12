#!/bin/sh

rm -fr ../../dash/
mkdir -p ../../dash/
npm run build
mv dist/* ../../dash/
