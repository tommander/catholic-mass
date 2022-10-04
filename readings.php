<?php
/**
 * @package OrderOfMass
 */

	if (!defined('OOM_BASE')) {
		die('This file cannot be viewed independently.');
	}

	/**
	 * Class that does something
	 */ 
	class MassReadings {
		private $cal = [];

		function __construct() {
			$year = date('Y');
//			$year = '2023';
			$calFile = __DIR__.DIRECTORY_SEPARATOR.'lectionaries'.DIRECTORY_SEPARATOR.'year'.$year.'.json';
			if (!file_exists($calFile)) {
				$this->cal = $this->calendar($year);
				file_put_contents($calFile, json_encode($this->cal));
			} else {
				$this->cal = $this->loadJson($calFile);
			}
		}

		public function sundayLabel() {
			$time = time();
			while (date('w', $time) != '0') {
				$time += 86400;
			}

			$date = date('d.m.Y', $time);

			foreach($this->cal as $dt) {
				foreach ($dt as $dat=>$id) {
					if ($dat == $date) {
						return $id;
					}
				}
			}
			return;
		}

		public function lectio() {
			$cid = $this->sundayLabel();
			if (!$cid) {
				return;
			}

			$lectFile = __DIR__.DIRECTORY_SEPARATOR.'lectionaries'.DIRECTORY_SEPARATOR.'lectionary.json';
			$lect = $this->loadJson($lectFile);
			if (count($lect) == 0) {
				return;
			}

			if (!array_key_exists($cid, $lect)) {
				return;
			}

			$sc = $this->sundayCycle(date('Y'));
			if (!array_key_exists($sc, $lect[$cid])) {
				return;
			}

			return $lect[$cid][$sc];
		}

		public function sundayCycle($year) {
			$mod = ($year % 3);
			return ($mod == 0 ? "C" : ($mod == 1 ? "A" : "B"));
		}

		public function weekdayCycle($year) {
			return ($year % 2 == 0) ? "II" : "I";
		}

		/**
		 * Loads a JSON lectionary file into an associative array
		 * 
		 * @param string $fileName Path to the file incl. full file name
		 * @return array Content of the file or an empty array
		 */
		private function loadJson(string $fileName):array {
			if (file_exists($fileName)) {
				$aFileCont = file_get_contents($fileName);
				if ($aFileCont !== FALSE) {
					$a = json_decode($aFileCont, true);
					if ($a !== NULL && is_array($a)) {
						return $a;
					}	
				}		
			}
			return [];
		}

		/**
		 * Builds a complete Sundays calendar for the given year
		 * 
		 * @see https://www.omnicalculator.com/everyday-life/easter
		 * @see https://www.omnicalculator.com/everyday-life/moon-phase
		 * @see https://catholic-resources.org/Lectionary/
		 */
		private function calendar($year) {
			$calendar = [];

			//First compute the first Sunday after January 6
			$dt = new DateTime($year.'-01-06');
			$odi = new DateInterval('P1D');
			$dt2 = $dt->add($odi);
			$cnt = 0;
			while ($dt2->format('w') != '0' && $cnt < 8) {
				$dt2->add($odi);
			}

			//Now we need to know the date of Ash Wednesday
			$equinox = new DateTime($year.'-03-21'); //Current year's spring equinox
			$fms = new DateTime('2000-01-06'); //Known full moon date
			$dftmp = $fms->diff($equinox)->format('%a'); //Days between
			while ($dftmp >= 29.53058770576) { //Repeatedly subtract one month
				$dftmp -= 29.53058770576;
			}
			if ($dftmp > 15.765294) { //If we are in the second half of the cycle, subtract one more month
				$dftmp -= 29.53058770576;
			}
			$dtfm = 0; //Compute days until next full moon
			while ($dftmp <= 14.765294) {
				$dtfm++;
				$dftmp++;
			}
			$ash = $equinox->add(new DateInterval("P${dtfm}D")); //Date of first full moon after spring equinox
			while ($ash->format('w') != '0') { //Find first sunday after that full moon
				$ash = $ash->add($odi);
			}
			$ash->sub(new DateInterval("P46D")); //Subtract 46 days (that is Ash Wednesday)

			$sund = 2;
			$owi = new DateInterval('P1W');

			//Add Feast of the Baptism of the Lord
			$calendar[] = [$dt2->format('d.m.Y') => "FOTBOTL"];
			$dt2->add($owi);

			//Add Ordinary Time Sundays until before Ash Wednesday
			while ($dt2->diff($ash)->format('%R') == '+') {
				$calendar[] = [$dt2->format('d.m.Y') => "SIOT${sund}"];
				$dt2->add($owi);
				$sund++;
			}

			//Now we need to know the beginning of Advent
			$ce = new DateTime($year.'-12-24'); //Christmas Eve
			$sbce = ($ce->format('w') == 0) ? 1 : 0;
			while ($sbce < 4) {
				$ce->sub($odi);
				if ($ce->format('w') == 0) {
					$sbce++;
				}
			}
			$ce->sub($odi);

			/* To skip or not to skip? We need to finish with 34th Sunday in O.T. */
			$dt3 = clone $dt2;
			$sund3 = $sund;
			for ($i=1; $i <= 13; $i++) { 
				$dt3->add($owi);				
			}
			while ($dt3->diff($ce)->format('%R') == '+') {
				$dt3->add($owi);		
				$sund3++;	
			}
			if ($sund3 < 35) {
				$sund++;
			}

			//Add 5 Lenten Sundays
			for ($i=1; $i <= 5; $i++) { 
				$calendar[] = [$dt2->format('d.m.Y') => "SOL${i}"];
				$dt2->add($owi);				
			}

			//Add Palm Sunday
			$calendar[] = [$dt2->format('d.m.Y') => "PaS"];
			$dt2->add($owi);				

			//Add Easter Sunday
			$calendar[] = [$dt2->format('d.m.Y') => "ES"];
			$dt2->add($owi);				

			//Add 6 Sundays until before Pentecost Sunday
			for ($i=2; $i <= 7; $i++) { 
				$calendar[] = [$dt2->format('d.m.Y') => "SOE${i}"];
				$dt2->add($owi);				
			}

			//Fill Sundays until before Advent
			while ($dt2->diff($ce)->format('%R') == '+') {
				$calendar[] = [$dt2->format('d.m.Y') => "SIOT${sund}"];
				$dt2->add($owi);		
				$sund++;	
			}

			//Add 4 Advent Sundays
			for ($i=1; $i <= 4; $i++) { 
				$calendar[] = [$dt2->format('d.m.Y') => "SOA${i}"];
				$dt2->add($owi);				
			}

			//Add 2 Sundays after Christmas
			if ($dt2->format('d') == '25') {
				$calendar[] = [$dt2->format('d.m.Y') => "SAC1B"];
				$dt2->add($owi);				
				$calendar[] = [$dt2->format('d.m.Y') => "SAC2"];
				$dt2->add($owi);				
			} else {
				for ($i=1; $i <= 2; $i++) { 
					$calendar[] = [$dt2->format('d.m.Y') => "SAC${i}"];
					$dt2->add($owi);				
				}
			}

			return $calendar;
		}		

	}
?>