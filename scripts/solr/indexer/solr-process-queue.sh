#!/bin/bash

if hash /usr/bin/php 2>/dev/null; then
    PHP_BIN="/usr/bin/php"
else
    PHP_BIN="/opt/php5/bin/php"
fi



$PHP_BIN -r \@phpinfo\(\)\; | grep --color=never 'PHP Version' -m 1



if [[ $# -ne 2 ]] ; then
    exit;
fi

case "$1" in
"production")
    JOB_PATH=/sites/library/scripts/solr/indexer/job.php
    ;;
"demo")
    JOB_PATH=/sites/library/scripts/solr/indexer/job.php
    ;;
"preprod")
    JOB_PATH=/sites/library_preprod/scripts/solr/indexer/job.php
    ;;
"testing")
    JOB_PATH=/sites/library_test/scripts/solr/indexer/job.php
    ;;
"development")
   JOB_PATH=./job.php
   ;;
esac

nbprocess=$(ps ax | grep 'php' | wc -l)
if [[ $nbprocess -gt 60 ]] ; then
  echo "Trop de process en cours"
else
	$PHP_BIN $JOB_PATH -e $1 --cron $2 -c episciences &
fi
