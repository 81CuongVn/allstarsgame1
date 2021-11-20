<?php
class BattleQueue extends Relation {
	static $paranoid	= true;

	public static function matchmakePoints($player) {
		$points	=	$player->level_user	* 150;
		$points	+=	$player->level		* 100;
		$points	+=	$player->wins		* 15;
		$points	+=	$player->draws		* 10;
		$points	-=	$player->losses		* 5;

		return $points;
	}

	public static function info() {
		$estimatedTime	= 0;
		$queueCount		= sizeof(self::all());

		if ($queueCount > 0) {
			if ($queueCount > 5) {
				if ($queueCount > 25) {
					if ($queueCount > 100) {
						$estimatedTime = 5;
					} else {
						$estimatedTime = 15;
					}
				} else {
					$estimatedTime = 60;
				}
			} else {
				$estimatedTime = 600;
			}
		} else {
			$estimatedTime = 900;
		}

		return [
			'queueCount'	=> $queueCount,
			'estimatedTime'	=> $estimatedTime
		];
	}
}
