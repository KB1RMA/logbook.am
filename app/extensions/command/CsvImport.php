<?php

namespace app\extensions\command;

use app\models\Callsigns;

class CsvImport extends \lithium\console\Command {

	public $file;
	public $delimiter = ",";

	public function run() {

		// We need a file
		if ( !$this->file ) {
			$this->out('Please specify a file with --file!');
			return;
		}
			 
		// ...and that file should be readable	 
		if ( !is_readable( $this->file ) ) {
			$this->out($this->file . ' is not readable!');
			return;
		}
		
		// okay let's go, biotch

		// Open the file
		if ( ($handle = fopen($this->file, "r")) ) {

			$linecount = -1;
			$row = 1;
			$successes = 0;
			$updates = 0;
			$newrecords = 0;
			$failures = 0;			
			
			// Count the number of lines so we can figure a percent complete
			while(!feof($handle)){
				$line = fgets($handle, 4096);
				$linecount += substr_count($line, PHP_EOL);
			}
			
			// Back to beginning of the file
			fseek($handle, 0);	

			$this->header("Importing $linecount records from $this->file");

			// Begin the progress indicator
			echo "Progress :      ";  

			// Loop through each row (line in the file)
			while ( $data = fgetcsv($handle, 1000, $this->delimiter)) {

				// Grab header from the first row
				if ( $row == 1) {
					$header = $data;
					if ( in_array("callsign", $header ) ) {
						$callsignIndex = array_search("callsign", $header);
					} else {
						$this->out('You need to specify a callsign column!');
						break;
						return;
					}
				}	

				// Create a callsign model for the row, set parameters, then save it
				if ( $row != 1 ) {
					
					try {
						// See if the call exists in the database
						$callsign = Callsigns::first(array(
							'conditions' => array(
								'callsign' => strtoupper($data[$callsignIndex])
								)
							));

						// If the call isn't already in the database, create a new record
						if ( !count($callsign) ) {
							$callsign = Callsigns::create();
							$newrecords++;
						} else {
							$updates++;
						}

						// Add properties to the record corresponding to each column in the CSV
						$num = count($data);				
						for ( $c=0; $c < $num; $c++) {
							if ( !empty($header[$c]) )
								$callsign[$header[$c]] = $data[$c];
						}

						// Check if this is a successful save or a failure
						if ( $callsign->save() )
							$successes++;

					} catch(Exception $e) {
						// Not catching any errors for the moment. Just supressing them
						$failures++;
					}

					// Output a percentage complete
					$percentComplete = number_format( ($row / $linecount ) * 100, 0 );

					// Remove last 5 characters outputted and re output so we update the percent complete
					echo "\033[5D";
					echo str_pad($percentComplete, 3, ' ', STR_PAD_LEFT) . " %";
				}

				$row++;
			}	
		
			fclose($handle); // Close the file

			$this->out("\n$updates records updated and $newrecords new records");

			if ( $failures )
				$this->out("$failures failures");

		}
	}

}
