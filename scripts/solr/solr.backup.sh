#!/bin/bash

CURL_BIN="/usr/bin/curl -u ccsd:ccsd12solr41"
BACKUP_DIR=/opt/solrIndexSnapshots/
CURL_URL="http://ccsdsolr1.in2p3.fr:8080/solr/$1/replication?command=backup&location=$BACKUP_DIR$1&numberToKeep=$2"
$CURL_BIN $CURL_URL