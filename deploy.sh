#!/bin/sh

hg pull
hg up stable2

DATE=`date +%Y-%m-%dT%H-%M`

hg tag "deployed-$DATE"
hg push
