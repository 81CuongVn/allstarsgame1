<?php
	class BattlePvp extends Relation {
		private	$_player	= null;

		function set_player($id) {
			$this->_player	= $id;
		}

		function enemy() {
			if(!$this->_player) {
				return false;
			}

			if($this->_player == $this->player_id) {
				$id	= $this->enemy_id;
			} else {
				$id	= $this->player_id;
			}

			return Player::find($id);
		}
	}