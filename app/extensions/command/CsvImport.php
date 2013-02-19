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

			$previousPercent = 0;

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
					
					// Add properties to the document corresponding to each column in the CSV
					$num = count($data);				

					for ( $c=0; $c < $num; $c++) {
						// Skip callsign
						if ( $c != $callsignIndex ) {
							if ( !empty($header[$c]) )
								$callsign[$header[$c]] = $data[$c];
						}	
					}
					
					$criteria = array('callsign' => strtoupper($data[$callsignIndex]));
					$options = array('upsert' => true);					
					$document = array('$set' => $callsign);
					
					try {

						if ( Callsigns::update($document, $criteria, $options) )
							$successes++;

					} catch (Exception $e) {

						$failures++;

					}

					// Output a percentage complete
					$percentComplete = number_format( ($row / $linecount ) * 100, 0 );

					// Remove last 5 characters outputted and re output so we update the percent complete
					if ( $percentComplete != $previousPercent ) {
						echo "\033[5D";
						echo str_pad($percentComplete, 3, ' ', STR_PAD_LEFT) . " %";
						$previousPercent = $percentComplete;
					}
				}

				$row++;
			}	
		
			fclose($handle); // Close the file

			$this->out("\n$successes records affected");

			if ( $failures )
				$this->out("$failures failures");

		}
	}

}
