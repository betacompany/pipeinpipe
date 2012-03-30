#!/bin/sh

hg pull
hg up stable2

DATE=`date +%Y-%m-%d`

hg tag "deployed-$DATE"