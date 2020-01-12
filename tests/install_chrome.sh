#!/bin/bash

export CHROME_SOURCE_URL=https://dl.google.com/dl/linux/direct/google-chrome-stable_current_amd64.deb
wget --no-verbose -O /tmp/$(basename $CHROME_SOURCE_URL) $CHROME_SOURCE_URL
dpkg -i /tmp/$(basename $CHROME_SOURCE_URL)
apt-get install -yf
