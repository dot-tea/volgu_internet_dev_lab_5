<?php
	function validate_date($date) {
		$code = 0;
		$date_pattern = "/^20\d{2}\-(0?[1-9]|1[012])\-((0?[1-9])|([12][0-9])|(3[01]))$/";
		if (preg_match($date_pattern, $date)) {
			$date_parts = explode("-",$date);
			$year = intval($date_parts[0]);
			$month = intval($date_parts[1]);
			$day = intval($date_parts[2]);
			$days_in_month = array(31,28,31,30,31,30,31,31,30,31,30,31);
			if (($month === 1) || ($month > 2)) {
				if ($day > $days_in_month[$month-1]) {
					$code = -1;
				}
			}
			else if ($month == 2) {
				$is_leap = false;
				if ((!($year % 4) && $year % 100) || !($year % 400))
					$is_leap = true;
				if (($is_leap && $day > 29) || (!$is_leap && $day >= 29))
					$code = -1;
			}
		}
		else {
			$code = -1;
		}
		return $code;
	}
?>