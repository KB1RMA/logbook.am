#!/bin/bash

# Download FCC ULS and import into mongodb

STOREDIR=./tmp/
ULS=http://wireless.fcc.gov/uls/data/complete/l_amat.zip
TMPCSV=./tmp/forimport.csv

# Header line to be inserted into top of CSV for import
HEADER=LicenseAuthority.authority,LicenseAuthority.fileNumber,Callsign,LicenseAuthority.licenseClass,LicenseAuthority.entityName,Person.givenName,Person.additionalName,Person.familyName,Address.postOfficeBoxNumber,Address.streetAddress,Address.locality,Address.region,Address.postalCode,LicenseAuthority.attention,LicenseAuthority.frn

# Download and unzip ULS
wget -O ${STOREDIR}uls.latest.zip $ULS 
unzip -o ${STOREDIR}uls.latest.zip -d $STOREDIR

# Use the haggalicious script to parse ULS data
python uls_parse.py > $TMPCSV

# Add "FCC ULS" as the license authority
sed -i 's/^/"FCC ULS" , /' $TMPCSV

# Add header to the file
sed -i "1i${HEADER}" $TMPCSV

