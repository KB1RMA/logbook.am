#!/bin/bash

# Download FCC ULS and import into mongodb

STOREDIR=./app/resources/tmp/uls
ULS=http://wireless.fcc.gov/uls/data/complete/l_amat.zip

# Header line to be inserted into top of CSV for import
HEADER=record_type,usi,uls_file_number,ebf_number,callsign,entity_type,licensee_id,entity_name,first_name,mi,last_name,suffix,phone,fax,email,street_address,city,state,zip,po_box,attention_line,sgin,frn,applicant_type_code,applicant_type_code_other,status_code,status_date

wget -O $STOREDIR.latest.zip \
		 $ULS 

unzip -o $STOREDIR.latest.zip -d $STOREDIR

# Replace pipes with commas and quotes for import to mongodb
sed -i 's/,/\\\,/g;s/|/","/g;s/\\,/,/g;s/^/"/;s/$/"/' $STOREDIR/EN.dat

# Add header to the file
sed -i "1i${HEADER}" $STOREDIR/EN.dat

mongoimport --db lookup_callsigns \
						--collection callsigns \
						--type csv \
						--headerline \
						--file $STOREDIR/EN.dat \
						--upsert \
						--upsertFields callsign

