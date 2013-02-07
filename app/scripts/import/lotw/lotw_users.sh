#!/bin/bash

# Downloads LoTW data from http://www.wd5eae.org/
# so those callsigns can be flagged

STOREDIR=./tmp/
LOTW=http://www.wd5eae.org/LoTW_Data.txt
TMPCSV=./tmp/lotwforimport.csv

HEADER=callsign,lotw_last_active

wget -O $TMPCSV $LOTW

# Delete any lines with slashes in them 
sed -i '/\//d' $TMPCSV

# encapsulate in quotes
sed -i 's/,/","/g;s/^/"/;s/$/"/' $TMPCSV

# Add header to the file
sed -i "1i${HEADER}" $TMPCSV

# Update callsigns with LoTW info
mongoimport --db lookup_callsigns \
	--collection callsigns \
	--type csv \
	--headerline \
	--file $TMPCSV \
	--upsert \
	--upsertFields callsign
