#!/bin/sh

cat ../main.css ../menu.css ../icons.css ../ui-controls.css | java -jar yuicompressor-2.4.7.jar --type css > ../all.css
