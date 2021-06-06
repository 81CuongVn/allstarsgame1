<?php
class Anime extends Relation {
	static	$always_cached	= true;

	public function description() {
		return AnimeDescription::find_first('anime_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], array('cache' => true));
	}

	public function characters($extra = '') {
		return Character::find('anime_id=' . $this->id . $extra, array('reorder' => 'ordem ASC'));
	}

	public function time_quest($quest) {
		$quest	= TimeQuest::find($quest);
		return $quest;
	}

	public function time_quests() {
		$result	= [];
		$quests	= TimeQuest::all(['cache' => true, 'reorder' => 'req_level ASC']);
		foreach ($quests as $quest) {
			if ($quest->anime_id && $quest->anime_id != $this->id) {
				continue;
			}

			$result[]	= $quest;
		}

		return $result;
	}

	public function pvp_quest($quest) {
		$quest	= PvpQuest::find($quest);
		return $quest;
	}

	public function pvp_quests() {
		$result	= [];
		$quests	= PvpQuest::all(['cache' => true, 'reorder' => 'req_level ASC']);
		foreach ($quests as $quest) {
			if ($quest->anime_id && $quest->anime_id != $this->id) {
				continue;
			}

			$result[]	= $quest;
		}

		return $result;
	}

	public function graduations($extra = '') {
		return Graduation::find('1=1 ' . $extra, ['cache' => true]);
	}

	public function equipment_positions() {
		return ItemPosition::find('anime_id=' . $this->id, ['cache' => true]);
	}

	public function attacks() {
		$result	= [];
		$items	= Recordset::query('
			SELECT
				a.item_id

			FROM
				item_descriptions a JOIN
				items b ON b.id=a.item_id

			WHERE
				a.anime_id=' . $this->id . ' AND
				b.item_type_id = 1 AND
				b.is_generic = 1 AND
				language_id=' . $_SESSION['language_id'], true);

		foreach ($items->result_array() as $item) {
			$instance	= Item::find($item['item_id'], array('cache' => true));
			$instance->set_anime($this->id);

			$result[]	= $instance;
		}

		return $result;
	}
}
