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

function generate_key() {
	return md5(microtime().serialize($_SERVER));
}

function password($str) {
	return password_hash($str, PASSWORD_BCRYPT);
}

function validPassword($str) {
    return	preg_match('/[a-z]/', $str)				// tem pelo menos uma letra minúscula
			&& preg_match('/[A-Z]/', $str)			// tem pelo menos uma letra maiúscula
			&& preg_match('/[0-9]/', $str)			// tem pelo menos um número
			&& preg_match('/^[\w$@]{6,}$/', $str);	// tem 6 ou mais caracteres
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

function make_tooltip($text, $width = 120) {
	return "<div style='min-width: {$width}px; padding: 5px;'>{$text}</div>";
}

function str_limit($value, $limit = 100, $end = '...') {
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

	if ($error) {
		return [
			'error'		=> $error,
			'status'	=> $status
		];
	} else {
		$decoded_response   = json_decode($response, TRUE);
		return [
			'status'	=> $status,
			'response'	=> $decoded_response ? $decoded_response : $response
		];
	}
}

function canBattle($player, $enemy) {
	if (
		$player->id == $enemy->id ||
		$player->user_id == $enemy->user_id ||
		$player->ip == $enemy->ip
	) {
		return false;
	} elseif (Recordset::query("SELECT `id` FROM `player_battle_pvps` WHERE (
		(`player_id`		= {$player->id}			AND `enemy_id`		= {$enemy->id})			OR
		(`player_id`		= {$enemy->id}			AND `enemy_id`		= {$player->id})		OR
		(`player_ip`		= '{$player->ip}'		AND `enemy_ip`		= '{$enemy->ip}')		OR
		(`player_ip`		= '{$enemy->ip}'		AND `enemy_ip`		= '{$player->ip}')		OR
		(`player_user_id`	= {$player->user_id}	AND `enemy_user_id`	= {$enemy->user_id})	OR
		(`player_user_id`	= {$enemy->user_id}		AND `enemy_user_id`	= {$player->user_id})
	) AND `created_at`		>= DATE_SUB(NOW(), INTERVAL 2 HOUR) LIMIT 10")->num_rows >= 5) {
		return false;
	}

	return true;
}

function now($mysql_format = FALSE) {
	return $mysql_format ? date('Y-m-d H:i:s') : time();
}

function get_time_difference($start, $end) {
	if (!is_numeric($start)) {
		$uts['start']	=	strtotime($start);
	} else {
		$uts['start']	= $start;
	}

	if (!is_numeric($end)) {
		$uts['end']	=	strtotime($end);
	} else {
		$uts['end']	=	$end;
	}

	if ($uts['start'] > $uts['end']) {
		return [
			'days'		=> 0,
			'hours'		=> 0,
			'minutes'	=> 0,
			'seconds'	=> 0
		];
	}

	if ($uts['start'] !== -1 && $uts['end'] !== -1) {
		if ($uts['end'] >= $uts['start']) {
			$diff	= $uts['end'] - $uts['start'];
			if ($days = intval((floor($diff/86400))))
				$diff	= $diff%86400;
			if ($hours = intval((floor($diff/3600))))
				$diff	= $diff%3600;
			if ($minutes = intval((floor($diff/60))))
				$diff	= $diff%60;
			$diff	= intval($diff);
			return [
				'days'		=> $days,
				'hours'		=> $hours,
				'minutes'	=> $minutes,
				'seconds'	=> $diff
			];
		} else {
			trigger_error("Ending date/time is earlier than the start date/time", E_USER_WARNING);
		}
	} else {
		trigger_error("Invalid date/time data detected", E_USER_WARNING);
	}

	return false;
}

function has_chance($val) {
	$rnd = get_chance();
	return $rnd <= $val ? true : false;
}

function get_chance() {
	return rand(1, 400) / 4;
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

function getIP() {
	// Get the forwarded IP if it exists
	if (array_key_exists('X-Forwarded-For', $_SERVER) && filter_var($_SERVER['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		return $_SERVER['X-Forwarded-For'];
	} elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}
}

function isProxy($ip) {
	if (!$ip || in_array($ip, ['127.0.0.1', '0.0.0.0'])) {
		return false;
	}

	$isProxy	= false;
	$check		= Recordset::query("SELECT id,proxy FROM proxy_ips WHERE ip = '{$ip}'");
	if (!$check->num_rows) {
		$data		= \proxycheck\proxycheck::check($ip, [
			'API_KEY'			=> PROXY_CHECK_KEY,		// Your API Key.
			'ASN_DATA'			=> 1,					// Enable ASN data response.
			'DAY_RESTRICTOR'	=> 7,					// Restrict checking to proxies seen in the past # of days.
			'VPN_DETECTION'		=> 1,					// Check for both VPN's and Proxies instead of just Proxies.
			'RISK_DATA'			=> 1,					// 0 = Off, 1 = Risk Score (0-100), 2 = Risk Score & Attack History.
			'INF_ENGINE'		=> 1,					// Enable or disable the real-time inference engine.
			'TLS_SECURITY'		=> 0,					// Enable or disable transport security (TLS).
			'QUERY_TAGGING'		=> 1,					// Enable or disable query tagging.
			'CUSTOM_TAG'		=> '',					// Specify a custom query tag instead of the default (Domain+Page).
			'BLOCKED_COUNTRIES'	=> [],					// Specify an array of countries or isocodes to be blocked.
			'ALLOWED_COUNTRIES'	=> []				 	// Specify an array of countries or isocodes to be allowed.
		]);

		$isProxy	= $data[$ip]['proxy'] == 'yes';

		Recordset::insert('proxy_ips', [
			'ip'			=> $ip,
			'proxy'			=> $isProxy ? 1 : 0
		]);
	} else {
		$proxy		= $check->row_array();
		$isProxy	= $proxy['proxy'] == 1;
	}

	return $isProxy;
}

function lastDayOfMonth($date) {
	return date("Y-m-t", strtotime($date));
}

function getGravatar($email, $s = 80, $d = 'mp', $r = 'g', $img = false, $atts = []) {
	$email	= md5(strtolower(trim($email)));
    $url	= "https://www.gravatar.com/avatar/{$email}?s={$s}&d={$d}&r={$r}";
    if ($img) {
        $url = '<img src="' . $url . '"';
        foreach ($atts as $key => $val) {
            $url .= ' ' . $key . '="' . $val . '"';
		}
        $url .= ' />';
    }

	return $url;
}
