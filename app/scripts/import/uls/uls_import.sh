#!/bin/bash

# Download FCC ULS and import into mongodb

STOREDIR=./tmp/
ULS=http://wireless.fcc.gov/uls/data/complete/l_amat.zip
TMPCSV=./tmp/ulsforimport.csv

# Header line to be inserted into top of CSV for import
HEADER=uls_file_number,callsign,license_class,first_name,mi,last_name,po_box,street_address,city,state,zip,attention,frn

# Download and unzip ULS
wget -O ${STOREDIR}uls.latest.zip $ULS 
unzip -o ${STOREDIR}uls.latest.zip -d $STOREDIR

# Use the haggalicious script to parse ULS data
python uls_parse.py > $TMPCSV

# Add header to the file
sed -i "1i${HEADER}" $TMPCSV

mongoimport --db lookup_callsigns \
	--collection callsigns \
	--type csv \
	--headerline \
	--file $TMPCSV \
	--upsert \
	--upsertFields callsign

