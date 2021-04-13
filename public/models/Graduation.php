<?php
	class Graduation extends Relation {
		static	$always_cached				= true;
		private	static $technique_cache	= [];

		function description($anime_id) {
			return GraduationDescription::find_first('graduation_id=' . $this->id . ' AND anime_id=' . $anime_id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
		}

		function has_requirement($player) {
			$ok				= true;
			$log			= '<ul class="requirement-list">';
			$error			= '<li class="error"><i class="fa fa-times fa-fw"></i> %result</li>';
			$success		= '<li class="success"><i class="fa fa-check fa-fw"></i> %result</li>';
			$quest_counters	= $player->quest_counters();

			if ($this->req_level) {
				$ok		= $this->req_level > $player->level ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.level', array('level' => $this->req_level)), $this->req_level > $player->level ? $error : $success);
			}
			if ($this->req_quest_count) {
				$ok		= $this->req_quest_count > $quest_counters->time_total ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.quest_count', array('count' => $this->req_quest_count)), $this->req_quest_count > $quest_counters->time_total ? $error : $success);
			}
			if ($this->req_training_points) {
				$ok		= $this->req_training_points > $player->training_total ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.training_points', array('count' => $this->req_training_points)), $this->req_training_points > $player->training_total ? $error : $success);
			}
			if ($this->req_wins_pvp) {
				$ok		= $this->req_wins_pvp > $player->wins_pvp ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.wins_pvp', [
					'count' => $this->req_wins_pvp
				]), $this->req_wins_pvp > $player->wins_pvp ? $error : $success);
			}
			if ($this->req_wins_npc) {
				$ok		= $this->req_wins_npc > $player->wins_npc ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.wins_npc', [
					'count' => $this->req_wins_npc
				]), $this->req_wins_npc > $player->wins_npc ? $error : $success);
			}
			if ($this->req_battles_pvp) {
				$ok		= $this->req_battles_pvp > ($player->wins_pvp + $player->losses_pvp + $player->draws_pvp) ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.battles_pvp', [
					'count' => $this->req_battles_pvp
				]), $this->req_battles_pvp > ($player->wins_pvp + $player->losses_pvp + $player->draws_pvp) ? $error : $success);
			}
			if ($this->req_league_wins) {
				$total_league_win	= 0;
				$player_rankeds		= PlayerRanked::find("player_id=".$player->id);
				foreach ($player_rankeds as $player_ranked) {
					$total_league_win += $player_ranked->win_total;
				}

				$ok		= $this->req_league_wins > $total_league_win ? false : $ok;
				$log	.= str_replace('%result', t('graduations.requirements.league_wins', array('count' => $this->req_league_wins)), $this->req_league_wins > $total_league_win ? $error : $success);
			}

			$log	.= '</ul>';

			return [
				'has_requirement' => $ok,
				'requirement_log' => $log
			];
		}
	}