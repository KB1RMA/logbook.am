#!/usr/bin/ruby

require 'rubygems'
require 'mongo'
require 'httparty'
require 'open-uri'
require 'json'

include Mongo
include HTTParty

# Connect to mongo
client = MongoClient
	.new('192.168.1.126')
	.db('lookup_callsigns')
	.collection('callsigns')

searchParams =  {
	'Address.region' => ARGV[0],
	'GeoCoordinates.lastGeocoded' => { '$exists' => false },
	'GeoCoordinates.lastFailure' => { '$exists' => false },
}

# URI we're sending the request to
url = "http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit=1&q="

# Find and iterate through all the records that need to be geocoded
# Sends a new query every time so I can have multiple scripts going at once
client.find(searchParams, :timeout => false) do |cursor|
	cursor.each do |document|

		# Build the address to be geocoded from the retrieved document
		address = ""
		address << document["Address"]["streetAddress"]
		address << ' '
		address << document["Address"]["locality"]
		address << ', '
		address << document["Address"]["region"]

		# Encode the string to be sent
		encoded = URI::encode("#{url}#{address}")

		# Send the request
		response = HTTParty.get(encoded)

		# If there are addresses returned, we can parse as JSON
		if response.body != "[]"
			result = JSON.parse(response.body)

			# Build hash to update the object from the response
			geoCoordinates = {
				:GeoCoordinates => {
					:latitude => result[0]["lat"],
					:longitude => result[0]["lon"],
					:lastGeocoded => Time.now,
					:geosource => result[0]["licence"],
				},
				"Address.county" => result[0]['address']['county'],
				:Location => [ "lng" => result[0]["lon"].to_f, "lat" => result[0]["lat"].to_f ]
			}

			# Output that we have been successful
			puts "#{document["Callsign"]} successfully geocoded with #{address}"

			# Update the document in the database
			client.update( { :_id => document["_id"] }, '$set' => geoCoordinates )

		else # We have failed in our quest
			puts "#{document["Callsign"] } failed with an address of #{address}"

			# Set a key indicating it has failed
			client.update( { :_id => document["_id"] }, '$set' => { :GeoCoordinates => { :lastFailure => Time.now } } )
		end
	end
end

