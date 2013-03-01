#!/bin/bash

# Download FCC ULS and import into mongodb

STOREDIR=./tmp/
ULS=http://wireless.fcc.gov/uls/data/complete/l_amat.zip
TMPCSV=./tmp/forimport.csv

# Header line to be inserted into top of CSV for import
HEADER=uls.fileNumber,callsign,uls.licenseClass,person.givenName,person.additionalName,person.familyName,address.postOfficeBoxNumber,address.streetAddress,address.locality,address.region,address.postalCode,uls.attention,uls.frn

# Download and unzip ULS
wget -O ${STOREDIR}uls.latest.zip $ULS 
unzip -o ${STOREDIR}uls.latest.zip -d $STOREDIR

# Use the haggalicious script to parse ULS data
python uls_parse.py > $TMPCSV

# Add header to the file
sed -i "1i${HEADER}" $TMPCSV

# to UTF8
iconv -f US-ASCII -t UTF-8 $TMPCSV > $TMPCSV.utf8 && mv $TMPCSV.utf8 $TMPCSV
