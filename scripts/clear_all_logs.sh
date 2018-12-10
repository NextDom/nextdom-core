#!/bin/sh

find /var/log/nextdom -type f -exec sh -c '>{}' \;
