<?php
class CharacterSpeciality extends Relation {
	static	$always_cached	= true;

	function image($path_only = false) {
		$path	= 'specialities/' . $this->character_id . '/' . $this->id . '.png';

		if ($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" alt="' . $this->description()->name . '" />';
		}
	}

	function effects() {
		$chances	= explode(',', $this->effect_chances);
		$effects	= ItemEffect::find('id IN (' . $this->item_effect_ids . ')', ['cache' => true]);

		foreach ($effects as $key => $effect) {
			$effect->chance		= $chances[$key];
			$effect->duration	= $this->effect_duration;
		}

		return $effects;
	}

	function description() {
		return CharacterSpecialityDescription::find_first('character_speciality_id=' . $this->id . ' AND language_id=' . $_SESSION['language_id'], ['cache' => true]);
	}

	function tooltip($player = null, $text_only = false) {
		return partial('shared/ability_speciality_tooltip', [
			'target'	=> $this,
			'effects'	=> $this->effects(),
			'player'	=> $player,
			'text_only'	=> $text_only
		]);
	}

	function has_requirement($player) {
		$ok		= true;
		$log	= [];

		return [
			'has_requirement'	=> $ok,
			'requirement_log'	=> $log
		];
	}
}
