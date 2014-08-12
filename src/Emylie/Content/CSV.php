<?php
/**
 * CSV used for anything to do with CSV files
 *
 * @copyright  2013 Neil Conlan
 * @version    Release: 1.0
 * @author     Neil Conlan <nconlan@clicko.com>
 */

namespace Emylie\Content {

	class CSV {

		public static function csvExport($fileName, $data, $headers = []) {

			$outputFile = DATA_DIR . "/csv_exports/".md5(uniqid('exportCsv:',true));

			$fh = fopen($outputFile, "w");

			if (count($headers)) {

				$line = implode('","', $headers);
				$line = '"'.$line.'"';
				$line .= PHP_EOL;

				if (!fwrite($fh, $line)) {

				}
			}

			foreach ($data as $d) {

				$line = implode('","', $d);
				$line = '"'.$line.'"';
				$line .= PHP_EOL;

				if (!fwrite($fh, $line)) {

				}
			}

			fclose($fh);

			header("Content-Description: File Transfer");
			header("Pragma: public"); // required
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header('Content-type: text/csv');
			header("Content-Disposition: attachment; filename=\"".addslashes($fileName)."\"" );
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($outputFile));
			ob_clean();
			flush();
			readfile( $outputFile );

			unlink($outputFile);
		}
	}

}