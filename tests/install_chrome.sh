#!/bin/bash

export CHROME_SOURCE_URL=https://www.slimjet.com/chrome/download-chrome.php?file=files%2F79.0.3945.88%2Fgoogle-chrome-stable_current_amd64.deb
wget --no-verbose -O /tmp/$(basename $CHROME_SOURCE_URL) $CHROME_SOURCE_URL
dpkg -i /tmp/$(basename $CHROME_SOURCE_URL)
apt-get update
apt-get install -yf
