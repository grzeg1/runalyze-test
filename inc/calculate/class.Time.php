<?php
/**
 * This file contains class::Time
 * @package Runalyze\Calculations
 */
/**
 * Class for standard operations for timestamps
 * @author Hannes Christiansen
 * @package Runalyze\Calculations
 */
class Time {
	/**
	 * Display the time as a formatted string
	 * @param int $time_in_s
	 * @param bool $show_days	[optional] Show days (default) or count hours > 24, default: true
	 * @param bool $show_zeros	[optional] Show e.g. '0:00:00' for 0, default: false, can be '2' for 0:00
	 * @param bool $show_seconds [optional] Show e.g. '32,00s' instead of 0:32 for any time less than one minute
	 * @return string
	 */
	public static function toString($time_in_s, $show_days = true, $show_zeros = false, $show_seconds = true) {
		if ($time_in_s < 0)
			return '&nbsp;';

		$string    = '';
		$time_in_s = round($time_in_s, 2); // correct float-problem with floor

		if ($show_zeros === true || $time_in_s>3600) {
			$string = floor($time_in_s/3600).':'.Helper::TwoNumbers(floor($time_in_s/60)%60).':'.Helper::TwoNumbers($time_in_s%60);
			if ($time_in_s - floor($time_in_s) != 0)
				$string .= ','.Helper::TwoNumbers(round(100*($time_in_s - floor($time_in_s))));
			return $string;
		}

		if ($show_zeros == 2)
			return (floor($time_in_s/60)%60).':'.Helper::TwoNumbers($time_in_s%60);

		if ($show_seconds && $time_in_s < 60)
			return number_format($time_in_s, 2, ',', '.').'s';

		if ($time_in_s >= 86400 && $show_days)
			$string = floor($time_in_s/86400).'d ';

		if ($time_in_s < 3600)
			$string .= (floor($time_in_s/60)%60).':'.Helper::TwoNumbers($time_in_s%60);
		elseif ($show_days)
			$string .= (floor($time_in_s/3600)%24).':'.Helper::TwoNumbers(floor($time_in_s/60)%60).':'.Helper::TwoNumbers($time_in_s%60);
		else
			$string .= floor($time_in_s/3600).':'.Helper::TwoNumbers(floor($time_in_s/60)%60).':'.Helper::TwoNumbers($time_in_s%60);

		if ($time_in_s - floor($time_in_s) != 0 && $time_in_s < 3600)
			$string .= ','.Helper::TwoNumbers(round(100*($time_in_s - floor($time_in_s))));

		return $string;
	}

	/**
	 * Calculate time in seconds from a given string (m:s|h:m:s)
	 * @param string $string
	 * @return int
	 */
	public static function toSeconds($string) {
		$TimeArray = explode(':', $string);

		switch (count($TimeArray)) {
			case 3:
				return ($TimeArray[0]*60 + $TimeArray[1])*60 + $TimeArray[2];
			case 2:
				return $TimeArray[0]*60 + $TimeArray[1];
			default:
				return $string;
		}

		if (count($TimeArray) == 2)
			return $TimeArray[0]*60 + $TimeArray[1];

		return $string;
	}

	/**
	 * Absolute difference in days between two timestamps
	 * @param int $time_1
	 * @param int $time_2 optional
	 * @return int
	 */
	static public function diffInDays($time_1, $time_2 = 0) {
		if ($time_2 == 0)
			$time_2 = time();

		return floor(abs(($time_1 - $time_2)/(3600*24)));
	}

	/**
	 * Calculates the difference in days of two dates (YYYY-mm-dd)
	 * @param string $date1
	 * @param string $date2
	 * @return int
	 */
	static public function diffOfDates($date1, $date2) {
		if (function_exists('date_diff')) // needs PHP >5.3.0
			return (int)date_diff(date_create($date1), date_create($date2))->format('%a');

		// TODO: Problem because of summer/winter-time
		return floor(abs(strtotime($date1) - strtotime($date2)) / (3600 * 24));
	}

	/**
	 * Is given timestamp from today?
	 * @param int $timestamp
	 * @return boolean
	 */
	static public function isToday($timestamp) {
		return date('d.m.Y') == date('d.m.Y', $timestamp);
	}

	/**
	 * Get string for daytime if not 0:00
	 * @param int $timestamp 
	 * @return string
	 */
	static public function daytimeString($timestamp) {
		return date('H:i', $timestamp) != '00:00' ? date('H:i', $timestamp).' Uhr' : '';
	}

	/**
	 * Get the timestamp of the start of the week
	 * @param int $time
	 */
	static public function Weekstart($time) {
		$w = date("w", $time);
		if ($w == 0)
			$w = 7;
		$w -= 1;
		return mktime(0, 0, 0, date("m",$time), date("d",$time)-$w, date("Y",$time));
	}

	/**
	 * Get the timestamp of the end of the week
	 * @param int $time
	 */
	static public function Weekend($time) {
		$start = self::Weekstart($time);
		return mktime(23, 59, 50, date("m",$start), date("d",$start)+6, date("Y",$start));
	}

	/**
	 * Get the name of a day
	 * @param string $w     date('w');
	 * @param bool $short   short version, default: false
	 */
	static public function Weekday($w, $short = false) {
		switch ($w%7) {
			case 0: return $short ? __('Sun') : __('Sunday');
			case 1: return $short ? __('Mon') : __('Monday');
			case 2: return $short ? __('Tue') : __('Tuesday');
			case 3: return $short ? __('Wed') : __('Wednesday');
			case 4: return $short ? __('Thu') : __('Thursday');
			case 5: return $short ? __('Fri') : __('Friday');
			case 6:
			default: return $short ? __('Sat') : __('Saturday');
		}
	}

	/**
	 * Get the name of the month
	 * @param string $m     date('m');
	 * @param bool $short   short version, default: false
	 */
	static public function Month($m, $short = false) {
		switch ($m) {
			case 1: return $short ? __('Jan') : __('January');
			case 2: return $short ? __('Feb') : __('February');
			case 3: return $short ? __('Mar') : __('March');
			case 4: return $short ? __('Apr') : __('April');
			case 5: return $short ? __('May') : __('May');
			case 6: return $short ? __('Jun') : __('June');
			case 7: return $short ? __('Jul') : __('July');
			case 8: return $short ? __('Aug') : __('August');
			case 9: return $short ? __('Sep') : __('September');
			case 10: return $short ? __('Oct') : __('October');
			case 11: return $short ? __('Nov') : __('November');
			case 12:
			default: return $short ? __('Dec') : __('December');
		}
	}

	/**
	 * Transform day and daytime to timestamp
	 * @param string $day
	 * @param string $time
	 * @return int
	 */
	static public function getTimestampFor($day, $time) {
		$post_day    = explode(".", $day);
		$post_time   = explode(":", $time);

		if (count($post_day) < 2) {
			$timestamp   = strtotime($day);

			if ($timestamp > 0)
				return $timestamp;

			$post_day[1] = date("m");
		}

		if (count($post_day) < 3)
			$post_day[2] = isset($_POST['year']) ? $_POST['year'] : date("Y");

		if (count($post_time) < 2)
			$post_time[1] = 0;

		return mktime((int)$post_time[0], (int)$post_time[1], 0, (int)$post_day[1], (int)$post_day[0], (int)$post_day[2]);
	}
}
