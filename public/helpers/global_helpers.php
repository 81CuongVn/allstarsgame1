<?php
function random_str($length) {
	$letters	= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$str		= "";
	for ($i = 1; $i <= $length; $i++) {
		$rand = rand(0, strlen($letters) - 1);
		$str .= $letters[$rand];
	}

	return $str;
}

function generate_key(){
	return md5(microtime().serialize($_SERVER));
}

function password($str) {
	return md5($str);
}

function between($value, $start, $end) {
	return $value >= $start && $value <= $end;
}

function display_money($number) {
	return highamount($number, 2);
}

function highamount($number, $decimals = 0) {
	return @number_format($number, $decimals, ',', '.');
}

function limit_text($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos   = array_keys($words);
        $text  = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}

function make_tooltip($text, $width = 120) {
	return "<div style='min-width: {$width}px; padding: 5px;'>{$text}</div>";
}

function str_limit($value, $limit = 100, $end = '...')
{
    if (mb_strwidth($value, 'UTF-8') <= $limit) {
        return $value;
    }

    return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
}

function format_date($date, $show_secs = FALSE){
	$time		= now();
	$date		= strtotime($date);

	$today		= date("d", $time);
	$yesterday	= date("d", $time);
	$tomorrow	= date("d", $time + 86400);

	$return		= "";
	if ($yesterday == date("d", $date)) {
		$return	= "ontem";
	} elseif ($today == date("d", $date)) {
		$return	= "hoje";
	} elseif ($tomorrow == date("d", $date)) {
		$return	= "amanhã";
	} else {
		$return	= "em " . date("d.m", $date);
	}

	if ($show_secs)	$return .= " às " . date("G:i:s", $date);
	else			$return .= " às " . date("G:i", $date);

	return $return;
}

function timeago($date) {
	$time = strtotime($date);
	$time_difference = (now() - 3600) - $time;
	if ($time_difference < 1)
		return 'agora mesmo';

	$condition = [
		12 * 30 * 24 * 60 * 60	=>  'ano(s)',
		30 * 24 * 60 * 60		=>  'mês(es)',
		24 * 60 * 60			=>  'dia(s)',
		60 * 60					=>  'hora(s)',
		60						=>  'minuto(s)',
		1						=>  'segundo(s)'
	];
	foreach ($condition as $secs => $str) {
		$d = $time_difference / $secs;
		if ($d >= 1) {
			$t = round($d);
			return $t . ' ' . $str . ' atrás';
		}
	}
}

function dateDiff($time1, $time2, $precision = 6) {
	// If not numeric then convert texts to unix timestamps
	if (!is_int($time1)) {
		$time1 = strtotime($time1);
	}
	if (!is_int($time2)) {
		$time2 = strtotime($time2);
	}

	// If time1 is bigger than time2
	// Then swap time1 and time2
	if ($time1 > $time2) {
		$ttime = $time1;
		$time1 = $time2;
		$time2 = $ttime;
	}

	// Set up intervals and diffs arrays
	$intervals = array('year','month','day','hour');
	$diffs = array();

	// Loop thru all intervals
	foreach ($intervals as $interval) {
		// Set default diff to 0
		$diffs[$interval] = 0;
		// Create temp time from time1 and interval
		$ttime = strtotime("+1 " . $interval, $time1);

		// Loop until temp time is smaller than time2
		while ($time2 >= $ttime) {
			$time1 = $ttime;
			$diffs[$interval]++;
			// Create new temp time from time1 and interval
			$ttime = strtotime("+1 " . $interval, $time1);
		}
	}

	$count = 0;
	$times = array();
	// Loop thru all diffs
	foreach ($diffs as $interval => $value) {
		// Break if we have needed precission
		if ($count >= $precision) {
			break;
		}
		// Add value and interval
		// if value is bigger than 0
		if ($value > 0) {
			switch($interval){
				case 'year':
					$interval = 'anos';
					break;
				case 'month':
					$interval = 'meses';
					break;
				case 'day':
					$interval = 'dias';
					break;
				case 'hour':
					$interval = 'horas';
					break;
			}
			// Add s if value is not 1
			if ($value != 1) {
				switch($interval){
					case 'year':
						$interval = 'anos';
						break;
					case 'month':
						$interval = 'meses';
						break;
					case 'day':
						$interval = 'dias';
						break;
					case 'hour':
						$interval = 'horas';
						break;
				}
			}
			// Add value and interval to times array
			$times[] = $value . " " . $interval;
			$count++;
		}
	}

	// Return string with times
	return implode(", ", $times);
}
function time_round($date){

	//Convert to date
	$datestr = $date;//Your date
	$date=strtotime($datestr);//Converted to a PHP date (a second count)

	//Calculate difference
	$diff=$date-now();//time returns current time in seconds
	$days=floor($diff/(60*60*24));//seconds/minute*minutes/hour*hours/day)
	$hours=round(($diff-$days*60*60*24)/(60*60));

	//Report
	return $days ." Dias e ". $hours ." horas";
}

function percent($p, $v) {
	if($p == 0) return 0;

	return round($v * ($p / 100), 0, PHP_ROUND_HALF_UP);
}

function percentf($p, $v) {
	if($p == 0) return 0;

	return $v * ($p / 100);
}

function as_percent($base, $value) {
	if ($base == $value) {
		return 100;
	}

	return $base * 100 / $value;
}
function global_message($message, $is_yaml = FALSE, $assigns = []) {
	$curl      = curl_init();
	$curl_options = [
		CURLOPT_URL				=> HIGHLIGHTS_SERVER . '/console/write/',
		CURLOPT_RETURNTRANSFER	=> TRUE,
		CURLOPT_ENCODING		=> '',
		CURLOPT_SSL_VERIFYPEER  => FALSE,
		CURLOPT_MAXREDIRS		=> 10,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=> 'POST',
		CURLOPT_POSTFIELDS      => http_build_query([
			($is_yaml ? 'yaml' : 'message')     => $message,
			'token'                             => HIGHLIGHTS_KEY,
			'assigns'                           => $assigns
		])
	];
	curl_setopt_array($curl, $curl_options);
	$response	= curl_exec($curl);
	$error		= curl_error($curl);
	$status		= curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($error)
		return [
			'error'		=> $error,
			'status'	=> $status
		];
	else {
		$decoded_response   = json_decode($response, TRUE);
		return [
			'status'	=> $status,
			'response'	=> $decoded_response ? $decoded_response : $response
		];
	}
}

function now($mysql_format = FALSE) {
	return $mysql_format ? date('Y-m-d H:i:s') : strtotime('+0 minute');
}

function get_time_difference( $start, $end ) {
	if(!is_numeric($start)) {
		$uts['start']	=	strtotime( $start );
	} else {
		$uts['start']	= $start;
	}

	if(!is_numeric($end)) {
		$uts['end']	=	strtotime( $end );
	} else {
		$uts['end']	=	$end;
	}

	if($uts['start'] > $uts['end']) {
		return array(
			'days'		=> 0,
			'hours'		=> 0,
			'minutes'	=> 0,
			'seconds'	=> 0
		);
	}

	if( $uts['start']!==-1 && $uts['end']!==-1 ) {
		if( $uts['end'] >= $uts['start'] ) {
			$diff	=	$uts['end'] - $uts['start'];
			if( $days = intval((floor($diff/86400))) )
				$diff = $diff % 86400;
			if( $hours = intval((floor($diff/3600))) )
				$diff = $diff % 3600;
			if( $minutes = intval((floor($diff/60))) )
				$diff = $diff % 60;
			$diff	=	intval( $diff );
			return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
		} else {
			trigger_error( "Ending date/time is earlier than the start date/time", E_USER_WARNING );
		}
	} else {
		trigger_error( "Invalid date/time data detected", E_USER_WARNING );
	}
	return( false );
}

function has_chance($val) {
	$rnd = rand(0, 400) / 4;
	return $rnd <= $val ? true : false;
}

function get_chance() {
	return rand(0, 400) / 4;
}

function array_random_key($arr) {
	$keys	= array_keys($arr);

	return $keys[floor(rand(0, sizeof($keys) - 1))];
}

function array_random_item($arr) {
	return $arr[array_random_key($arr)];
}

function frand($min = 0, $max = null) {
	if (is_null($max)) {
		$max	= getrandmax();
	}

	return $min + ((float)rand()/(float)getrandmax()) * $max;
}

function format_time($seconds) {
	$hours		= 0;
	$minutes	= 0;

	while ($seconds >= 3600) {
		++$hours;
		$seconds -= 3600;
	}
	while ($seconds >= 60) {
		++$minutes;
		$seconds -= 60;
	}

	return [
		'hours'		=> sprintf("%02s", $hours),
		'minutes'	=> sprintf("%02s", $minutes),
		'seconds'	=> sprintf("%02s", $seconds),
		'string'	=> sprintf("%02s", $hours) . ":" . sprintf("%02s", $minutes) . ":" . sprintf("%02s", $seconds)
	];
}