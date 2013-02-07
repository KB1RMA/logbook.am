import os, sys, string
from datetime import datetime

def parseDateTime(tstmp):
    return datetime.strptime(tstmp, "%m/%d/%Y");

records = {}

fd = open("tmp/HD.dat")
now = datetime.now()

for line in fd.xreadlines():
    line = line.strip()
    fields = line.split("|")
    record, status, call, expire = fields[1], fields[5], fields[4], fields[8]
    if status != "A": 
        continue
    try:
        expire = parseDateTime(expire)
    except:
        print "failed to parse: %r" % line  
        expire = parseDateTime("1/1/1900")
    if expire > now:
        records[record] = []
fd = open("tmp/AM.dat")


stats = { '':0, 'A':0, 'T': 0, 'G':0, 'E':0 , 'N':0}
for line in fd.xreadlines():
    line = line.strip()
    fields = line.split("|")
    if records.has_key(fields[1]):
        records[fields[1]] = [ fields[1], fields[5] ]
        stats[fields[5]]+=1

fd = open("tmp/EN.dat")

for line in fd.xreadlines():
    line = line.strip()
    fields = line.split("|")
    if records.has_key(fields[1]):
        first, middle, last, Street, City, State, Zip, POBox, Atten, FRN = fields[8], fields[9], fields[10], fields[15], fields[16], fields[17], fields[18], fields[19], fields[20], fields[22]
        if Street == "" and len(POBox) > 0:
            Street = "PO Box %s" % POBox

        records[fields[1]]+=[first, middle, last, Street, City, State, Zip, Atten, FRN]

for record in records.keys():
    print ", ".join([ "'%s'" % field for field in records[record]])

print stats
