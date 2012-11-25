#!/bin/sh

WD=`pwd`

hg pull
hg up stable2

DATE=`date +%Y-%m-%dT%H-%M`

hg tag "deployed-$DATE"
hg push

cd main/css/yui
sh compile.sh

cd ../../js/closure
sh compile.sh

cd pwd
