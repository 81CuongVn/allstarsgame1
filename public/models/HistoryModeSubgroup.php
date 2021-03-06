<?php
	class HistoryModeSubgroup extends Relation {
		static	$always_cached	= true;

		function group() {
			return HistoryModeGroup::find($this->history_mode_group_id, ['cache' => true]);
		}

		function description($language_id = null) {
			return HistoryModeSubgroupDescription::find_first('history_mode_subgroup_id=' . $this->id . ' AND language_id=' . ($language_id ? $language_id : $_SESSION['language_id']));
		}

		function image() {
			return "/adventure/subgroup/" . $this->id . ".png";
		}

		function npcs($player) {
			return HistoryModeNpc::find('history_mode_subgroup_id=' . $this->id . ' AND faction_id=1', ['cache' => true, 'reorder' => 'sorting ASC']);
		}

		function completed($player) {
			foreach ($this->npcs($player) as $npc) {
				$npc->set_player($player);

				if (!$npc->killed()) {
					return false;
				}
			}

			return true;
		}
	}