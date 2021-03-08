<?php
class User extends Relation {
	static			$paranoid		= TRUE;
	static			$password_field	= 'password';
	private static	$instance		= NULL;
	
	protected function before_update() {
		if ($this->is_next_level()) {
			/*while ($this->is_next_level()) {
				$this->level	+= 1;
				$this->exp		-= $this->level_exp();
			}*/
			$this->level	+= 1;
			$this->exp		-= $this->level_exp();
		}
	}
	protected function after_assign() {
		if ($this->banned && !$_SESSION['universal']) {
			$_SESSION['loggedin']			= FALSE;
			$_SESSION['user_id']	        = NULL;
			$_SESSION['player_id']			= NULL;
			$_SESSION['universal']	        = FALSE;
			$_SESSION['skip_maintenance']	= FALSE;
		}
	}
	function character_theme_image($image_id) {
		$user_image = UserCharacterThemeImage::find_first("user_id=" . $this->id . " AND character_theme_image_id=" . $image_id);
		
		if ($user_image) {
			return TRUE;
		} else {
			return FALSE;	
		}
	}
	function level_exp() {
		return (1000 / 5) * $this->level;
	}
	function is_next_level() {
		return $this->exp >= $this->level_exp();
	}
	function players() {
		return Player::find('user_id=' . $this->id);
	}
	function spend($amount) {
		$this->credits	-= $amount;
		$this->save();
	}
	function earn($amount) {
		$this->credits	+= $amount;
		$this->save();
	}
	function round_points($points) {
		$this->round_points	+= $points;
		$this->save();
	}
	function exp($amount) {
		$this->exp	+= $amount;
		$this->save();
	}
	function quest_counters() {
		return UserQuestCounter::find_first('user_id=' . $this->id);
	}
	function is_theme_bought($theme_id) {
		return UserCharacterTheme::find_first('user_id=' . $this->id . ' AND character_theme_id=' . $theme_id) ? TRUE : FALSE;
	}
	function is_character_bought($character_id) {
		return UserCharacter::find_first('user_id=' . $this->id . ' AND character_id=' . $character_id) ? TRUE : FALSE;
	}
	function is_headline_bought($headline_id) {
		return UserHeadline::find_first('user_id=' . $this->id . ' AND headline_id=' . $headline_id) ? TRUE : FALSE;
	}
	function is_theme_image_bought($theme_image_id) {
		return UserCharacterThemeImage::find_first('user_id=' . $this->id . ' AND character_theme_image_id=' . $theme_image_id) ? TRUE : FALSE;
	}
	function headlines() {
		return UserHeadline::find('user_id=' . $this->id . ' group by headline_id');
	}
	function account_quests() {
		return UserDailyQuest::find('user_id=' . $this->id ." AND complete=0");
	}
	static function set_instance($user) {
		User::$instance	= $user;
	}
	static function &get_instance() {
		return User::$instance;
	}
}
