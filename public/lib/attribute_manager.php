<?php
trait AttributeManager {
	public	$enemy_intimidation	= 0;
	private	$attrRate			= [];

	public function __construct() {
		global $attrRate;

		$this->attrRate = $attrRate;
	}

    function ability() {
        if (!$this->character_ability_id) {
            return false;
        }

		$player_character_ability = PlayerCharacterAbility::find_first("character_ability_id=".$this->character_ability_id." and player_id='".$this->id."'");
        if ($player_character_ability) {
            return $player_character_ability;
        } else {
            return CharacterAbility::find_first($this->character_ability_id);
        }
    }

    function speciality() {
        if (!$this->character_speciality_id) {
            return false;
        }

		$player_character_speciality = PlayerCharacterSpeciality::find_first("character_speciality_id=".$this->character_speciality_id." and player_id='".$this->id."'");
        if ($player_character_speciality) {
            return $player_character_speciality;
        } else {
            return CharacterSpeciality::find_first($this->character_speciality_id);
        }

    }

    function for_life($max = false, $raw = false) {
        $total = 1000;

        if (isset($this->organization_map_object_id) && $this->organization_map_object_id) {
            $object = OrganizationMapObject::find_first($this->organization_map_object_id);

            if ($object->kind == 'sharednpc') {
                $total = $object->max_life;
            }
        }

        if (!$raw) {
            $total	+= $this->attributes()->sum_for_life;
        }

        if ($max) {
            return $total;
        } else {
            return $total - $this->less_life;
        }
    }

    function for_mana($max = false, $raw = false) {
        $total = 20;

        if (isset($this->organization_map_object_id) && $this->organization_map_object_id) {
            $object = OrganizationMapObject::find_first($this->organization_map_object_id);

            if ($object->kind == 'sharednpc') {
                $total = 9999;
            }
        }

        if (!$raw) {
            $total	+= $this->attributes()->sum_for_mana;
        }

        if ($max) {
            return $total;
        } else {
            return $total - $this->less_mana;
        }
    }

    function for_stamina($max = false, $raw = false) {
        $total	= 10;
        if ($this->level >= 2) {
            $total	+= floor($this->level / 2) * 2;
		}

        if (!$raw) {
            $effects	= $this->get_parsed_effects();
            $total		+= $effects['bonus_stamina_max'];
        }

        if ($max) {
            return $total;
        } else {
            return $total - $this->less_stamina;
        }
    }

    function for_atk($raw = false) {
		global $attrRate;

		if ($raw) {
            return $this->character()->for_atk;
		}

        $effects	= $this->get_parsed_effects();
        $base		= $this->character()->for_atk + $this->attributes()->sum_for_atk + (($this->attributes()->for_atk + $this->for_atk) / $attrRate['for_atk']);
        $value		= $base + $effects['for_atk'] + percent($effects['for_atk_percent'], $this->character()->for_atk);

        if ($this->less_life >= 500) {
            $value		+= $effects['attack_half_life'] + percent($effects['attack_half_life_percent'], $base);
        }

		// return $value < 0 ? 0 : floor($value);
		return round($value, 2);
    }

    function for_def($raw = false) {
		global $attrRate;

        if ($raw) {
            return $this->character()->for_def;
		}

        $effects	= $this->get_parsed_effects();
        $base		= $this->character()->for_def + $this->attributes()->sum_for_def + (($this->attributes()->for_def + $this->for_def) / $attrRate['for_def']);
        $value		= $base + $effects['for_def'] + percent($effects['for_def_percent'], $base);

        if ($this->less_life >= 500) {
            $value		+= $effects['defense_half_life'] + percent($effects['defense_half_life_percent'], $base);
        }

        // return $value < 0 ? 0 : floor($value);
		return round($value, 2);
    }

    function for_crit() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $value		= $this->character()->for_crit + $this->attributes()->sum_for_crit + (($this->for_crit + $this->attributes()->for_crit) / $attrRate['for_crit']) + percent($effects['for_crit_percent'], $this->character()->for_crit) + $effects['for_crit'];
        return $value < 0 ? 0 : round($value, 2);
    }

    function for_crit_inc() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $base		= 10;

        if (has_chance(abs($effects['reduce_critical_damage']))) {
            $base	/= 2;
        }

        return $base  + (($this->attributes()->for_inc_crit + $this->for_inc_crit) / $attrRate['for_crit_inc']) + $this->attributes()->sum_for_inc_crit + $effects['for_crit_inc'] + percent($effects['for_crit_inc_percent'], $base);
    }

    function for_abs() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $value		= $this->character()->for_abs + $this->attributes()->sum_for_abs + (($this->for_abs + $this->attributes()->for_abs) / $attrRate['for_abs']) + percent($effects['for_abs_percent'], $this->character()->for_abs) + $effects['for_abs'];

        return $value < 0 ? 0 : round($value, 2);
    }

    function for_abs_inc() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $base		= 10;

        if (has_chance(abs($effects['enemy_absorb_reduction']))) {
            $base	/= 2;
        }

        return $base + (($this->attributes()->for_inc_abs + $this->for_inc_abs) / $attrRate['for_abs_inc']) + $this->attributes()->sum_for_inc_abs + $effects['for_abs_inc'] + percent($effects['for_abs_inc_percent'], $base);
    }

    function for_prec() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $value		= $this->character()->for_prec + $this->attributes()->sum_for_prec + (($this->for_prec + $this->attributes()->for_prec) / $attrRate['for_prec']) + percent($effects['for_prec_percent'], $this->character()->for_prec) + $effects['for_prec'];

        return $value < 0 ? 0 : $value;
    }

    function for_init() {
		global $attrRate;

        $effects	= $this->get_parsed_effects();
        $value		= $this->character()->for_init + $this->attributes()->sum_for_init + (($this->for_init + $this->attributes()->for_init) / $attrRate['for_init']) + percent($effects['for_init_percent'], $this->character()->for_init) + $effects['for_init'];

        return $value < 0 ? 0 : $value;
    }
}
