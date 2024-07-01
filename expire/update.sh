#!/bin/bash
# copy latest versions from development repository

# replace VuFind installation path
sed 's#^VUFIND_HOME=.*#VUFIND_HOME=/usr/local/vufind#' < ~/vufind/expire.sh > expire.sh
