<?php
class User extends Relation {
	static			$paranoid		= TRUE;
	static			$password_field	= 'password';
	private static	$instance		= NULL;

	protected function before_update() {
		if ($this->is_next_level()) {
			while ($this->is_next_level()) {
				$this->exp			-= $this->level_exp();
				$this->level		+= 1;
			}

			$player	= Player::get_instance();
			if ($player) {
				// verifica o level da conta do jogador
				$player->achievement_check('level_account');
				$player->check_objectives('level_account');
			}
		}
	}

	protected function after_assign() {
		if ($this->exp < 0) {
			$this->exp = 0;
			$this->save();
		}
	}

	public function hasBanishment() {
		// Verificar banimento ativo
		$banishment	= Banishment::find_last("type = 'user' and user_id = " . $this->id);
		if ($banishment && between(
			now(),
			strtotime($banishment->created_at),
			strtotime($banishment->finishes_at)
		)) {
			return $banishment;
		}

		return false;
	}

	public function character_theme_image($image_id) {
		$user_image = UserCharacterThemeImage::find_first("user_id=" . $this->id . " AND character_theme_image_id=" . $image_id);
		if ($user_image) {
			return true;
		} else {
			return false;
		}
	}

	public function level_exp() {
		return (1000 / 5) * $this->level;
	}

	public function is_next_level() {
		return $this->exp >= $this->level_exp() && $this->level < MAX_LEVEL_USER;
	}

	public function players() {
		return Player::find('user_id=' . $this->id);
	}

	public function total_players() {
		return Recordset::query("SELECT COUNT(id) AS total FROM players WHERE removed = 0 and user_id=" . $this->id)->row()->total;
	}

	public function spend($amount) {
		$this->credits	-= $amount;
		$this->save();
	}

	public function earn($amount) {
		$this->credits	+= $amount;
		$this->save();
	}

	public function round_points($points) {
		$this->round_points	+= $points;
		$this->save();
	}

	public function exp($amount) {
		$this->exp	+= $amount;
		$this->save();
	}

	public function quest_counters() {
		return UserQuestCounter::find_first('user_id=' . $this->id);
	}

	public function is_theme_bought($theme_id) {
		return UserCharacterTheme::find_first('user_id=' . $this->id . ' AND character_theme_id=' . $theme_id) ? TRUE : FALSE;
	}

	public function is_character_bought($character_id) {
		return UserCharacter::find_first('user_id=' . $this->id . ' AND character_id=' . $character_id) ? TRUE : FALSE;
	}

	public function is_headline_bought($headline_id) {
		return UserHeadline::find_first('user_id=' . $this->id . ' AND headline_id=' . $headline_id) ? TRUE : FALSE;
	}

	public function is_theme_image_bought($theme_image_id) {
		return UserCharacterThemeImage::find_first('user_id=' . $this->id . ' AND character_theme_image_id=' . $theme_image_id) ? TRUE : FALSE;
	}

	public function headlines() {
		return UserHeadline::find('user_id=' . $this->id . ' group by headline_id');
	}

	public function account_quests() {
		return UserDailyQuest::find('user_id=' . $this->id ." AND complete=0");
	}

	public function is_admin() {
		return $this->admin >= 2;
	}

	public function is_mod() {
		return $this->admin;
	}

	public function is_online() {
		return is_user_online($this->id);
	}

	public function update_online() {
		// Salva última ação no jogo
		$this->last_activity = now();
		$this->save();

		$redis = new Redis();
		if ($redis->pconnect(REDIS_SERVER, REDIS_PORT)) {
			$redis->auth(REDIS_PASS);
			$redis->select(0);

			$redis->set('user_' . $this->id . '_online', now(true));
		}
	}

	public function password_check($password) {
		$att	= true;
		$check	= false;

		$size	= strlen($this->password);
		switch ($size) {
			case 32:
				$check		= md5($password) == $this->password;
				break;
			case 41:
				$mysqlPass	= '*' . strtoupper(sha1(sha1($password, true)));
				$check		= $mysqlPass == $this->password;
				break;
			default:
				$att		= false;
				$check		= password_verify($password, $this->password);
				break;
		}

		if ($att) {
			$update	= User::find($this->id);
			$update->password	= $password;
			$update->save();
		}

		return $check;
	}

	static function set_instance($user) {
		User::$instance	= $user;
	}

	static function &get_instance() {
		return User::$instance;
	}
}
