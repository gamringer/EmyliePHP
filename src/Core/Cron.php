<?php

namespace Emylie\Core {

	class Cron{

		/**
		 * This method will create the cron lock file for this cron job
		 *
		 * @return bool if the lock was set or not
		 */
		public static function createCronLock($lockFile) {

			if (!file_exists($lockFile)) {

				// create lock file
				$fh = fopen($lockFile, 'w');
				// closing will write file to disc
				fclose($fh);

				if (file_exists($lockFile)) {
					// lock created
					echo "Lock file ".$lockFile." was created " . PHP_EOL;
					return true;
				} else {
					echo "ERROR creating lock file " . $lockFile . ". Check folder permissions." . PHP_EOL;
					return false;
				}
			}

			// lock is still there
			echo "Lock file still exists: ".$lockFile." still exists. Skipping... " . PHP_EOL;
			return false;
		}

		/**
		 * This method will delete the cron lock file
		 *
		 * @return bool if the lock was set or not
		 */
		public static function deleteCronLock($lockFile) {
			if (unlink($lockFile)) {
				echo "Deleted lock file: " . $lockFile . PHP_EOL;
				return true;
			} else {
				echo "COULD NOT DELETE lock file: " . $lockFile . PHP_EOL;
				return false;
			}
		}

	}
}
