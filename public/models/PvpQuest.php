<?php
	class PvpQuest extends Relation {
		static	$always_cached	= true;
		private	$_anime_id		= 0;
		private	$qtd_base		= 60;
		private	$grad_base		= 50;

		function set_anime($anime) {
			if (is_numeric($anime)) {
				$this->_anime_id	= $anime;
			} else {
				$this->_anime_id	= $anime->id;
			}
		}

		function description() {
			return PvpQuestDescription::find_first('pvp_quest_id=' . $this->id . ' AND anime_id=' . $this->_anime_id . ' AND language_id=' . $_SESSION['language_id']);
		}

		function currency() {
			return $this->exp() / 2;
		}

		function exp() {
			$quantity	= 
				$this->req_same_level +
				$this->req_low_level +
				$this->req_kill_wo_amplifier +
				$this->req_kill_wo_buff +
				$this->req_kill_wo_ability +
				$this->req_kill_wo_speciality;

			return ($this->grad_base * $this->req_graduation_sorting) + ($quantity * $this->qtd_base);
		}

		function training() {
			return $this->exp() * 2.5;
		}
	}