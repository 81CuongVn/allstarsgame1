<?php
class NpcInstance {
	use				BattleTechniqueLocks;
	use				EffectManager;
	use				AttributeManager;

	public	$less_life					= 0;
	public	$less_mana					= 0;
	public	$less_stamina				= 0;
	private	$gauge						= 0;
	public	$level						= 1;
	public	$id							= 0;

	public	$character_ability_id		= 0;
	public	$character_speciality_id	= 0;
	public	$battle_npc_challenge		= 0;
	public	$battle_npc_id				= 0;
	public	$battle_pvp_id				= 0;
	public	$faction_id					= 0;
	public	$guild_id			= 0;
	public	$character_id				= 0;

	public	$name						= '';

	public	$for_atk					= 0;
	public	$for_def					= 0;
	public	$for_crit					= 0;
	public	$for_abs					= 0;
	public	$for_prec					= 0;
	public	$for_init					= 0;
	public	$for_inc_crit				= 0;
	public	$for_inc_abs				= 0;

	# History mode specific npc
	public	$specific_image				= false;
	public	$specific_id				= 0;

	private	$attacks					= [];

	private	$anime						= null;
	private	$character					= null;
	private	$character_theme			= null;
	private	$theme_image				= 0;
	private	$_attributes				= null;
	private $_pet_id					= null;

	# map npcs
	public $guild_map_object_id	= null;
	public $shared_less_life			= 0;

	function __construct($player, $anime_id_for_generics = null, $theme_ids = [],
						$specific_ability_id = null, $specific_speciality_id = null, $specific_pet_id = null,
						$is_challenge = null, $character_id = null, $character_theme_id = null, $guild_map_object_id = null) {

		if ($anime_id_for_generics) {
			$animes					= Anime::find('id='. $anime_id_for_generics .' AND active=1', ['cache' => true]);
		} else {
			$animes					= Anime::find('active=1', ['cache' => true]);
		}
		$anime						= $animes[rand(0, sizeof($animes) - 1)];

		if ($character_id) {
			$characters				= $anime->characters(' AND id='. $character_id);
		} else {
			$characters				= $anime->characters(' AND active=1');
		}
		$character					= $characters[rand(0, sizeof($characters) - 1)];

		if ($character_theme_id) {
			$themes					= $character->themes(" AND id=".$character_theme_id);
		} else {
			$themes					= $character->themes(' AND active=1');
		}
		$theme						= $themes[rand(0, sizeof($themes) - 1)];

		$images						= CharacterThemeImage::find('active = 1 and character_theme_id = ' . $theme->id, ['cache' => true]);
		$image						= $images[rand(0, sizeof($images) - 1)];

		$this->anime				= $anime;
		$this->character			= $character;
		$this->character_theme		= $theme;
		$this->theme_image			= $image;
		$this->faction				= Faction::find_first('active=1', ['reorder' => 'RAND()']);

		$this->name					= $this->character->description()->name;
		$this->level				= $player->level;
		$this->uid					= uniqid(uniqid('', true), true);
		$this->id					= str_replace('.', '-', $this->uid);
		$this->faction_id			= $this->faction->id;
		$this->character_id			= $this->character->id;
		$this->guild_map_object_id	= $guild_map_object_id;

		if ($this->guild_map_object_id) {
			$map_object = $this->guild_map_object_id;
			$map_object_session = GuildMapObjectSession::find_first('player_id=0 AND guild_accepted_event_id=' . $player->guild_accepted_event_id . ' AND guild_id=' . $player->guild_id . ' AND guild_map_object_id=' . $this->guild_map_object_id);
			if ($map_object_session) {
				$this->less_life = $map_object_session->less_life;
			} else {
				$this->less_life = 0;
			}
		} else {
			$map_object = null;
		}

		$character		= $player->character();
		$total_points	= $player->for_atk() + $player->for_def() + $player->for_crit() + $player->for_abs() + $player->for_prec() + $player->for_init();

		if ($is_challenge) {
			$challenge  	= PlayerChallenge::find_first('player_id='. $player->id .' AND challenge_id='.$is_challenge." AND complete=0");
			$total_points 	= round($total_points + ($challenge->quantity * 1.5));

			if ($challenge->quantity % 5 == 0) {
				$total_hp = $challenge->quantity * 10;
			} else {
				if ($challenge->quantity > 25) {
					$total_hp = ($challenge->quantity - 25)  * 10;
				} else {
					$total_hp = 0;
				}
			}
			$total_mana		= $challenge->quantity % 5 == 0  ? $challenge->quantity / 5 : 0;

		} else {
			$total_hp		= 0;
			$total_mana		= 0;
		}

		$total_points	-= $character->for_atk + $character->for_def + $character->for_crit + $character->for_abs + $character->for_prec + $character->for_init;
		$types			= [
			[50, 10, 10, 10, 10, 10],
			[10, 50, 10, 10, 10, 10],
			[10, 10, 50, 10, 10, 10],
			[10, 10, 10, 50, 10, 10],
			[10, 10, 10, 10, 50, 10],
			[10, 10, 10, 10, 10, 50]
		];

		$choosen_type	= $types[rand(0, sizeof($types) - 1)];

		$this->character_ability_id		= $specific_ability_id ? $specific_ability_id : CharacterAbility::find_first('character_id=' . $this->character->id, ['reorder' => 'RAND()'])->id;
		$this->character_speciality_id	= $specific_speciality_id ? $specific_speciality_id : CharacterSpeciality::find_first('character_id=' . $this->character->id, ['reorder' => 'RAND()'])->id;

		if (is_null($specific_pet_id)) {
			$this->_pet_id				= Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()'])->id;
		} else {
			$this->_pet_id				= $specific_pet_id;
		}

		$this->clear_effects();

		$at	= new stdClass();

		$at->for_atk							= 0;
		$at->for_def							= 0;
		$at->for_crit							= 0;
		$at->for_abs							= 0;
		$at->for_prec							= 0;
		$at->for_init							= 0;
		$at->for_inc_crit						= 0;
		$at->for_inc_abs						= 0;

		$at->sum_at_for							= 0;
		$at->sum_at_int							= 0;
		$at->sum_at_res							= 0;
		$at->sum_at_agi							= 0;
		$at->sum_at_dex							= 0;
		$at->sum_at_vit							= 0;
		$at->sum_for_life						= $total_hp;
		$at->sum_for_mana						= $total_mana;
		$at->sum_for_stamina					= 0;
		$at->sum_for_atk						= percent($choosen_type[0], $total_points / 2);
		$at->sum_for_def						= percent($choosen_type[1], $total_points / 2);
		$at->sum_for_hit						= 0;
		$at->sum_for_init						= percent($choosen_type[5], $total_points / 2);
		$at->sum_for_crit						= percent($choosen_type[2], $total_points / 2);
		$at->sum_for_inc_crit					= 0;
		$at->sum_for_abs						= percent($choosen_type[3], $total_points / 2);
		$at->sum_for_inc_abs					= 0;
		$at->sum_for_prec						= percent($choosen_type[4], $total_points / 2);
		$at->sum_for_inti						= 0;
		$at->sum_for_conv						= 0;


		$at->generic_technique_damage			= 0;
		$at->unique_technique_damage			= 0;
		$at->defense_technique_extra			= 0;

		$at->sum_bonus_food_discount			= 0;
		$at->sum_bonus_weapon_discount			= 0;
		$at->sum_bonus_luck_discount			= 0;
		$at->sum_bonus_mana_consume				= 0;
		$at->sum_bonus_cooldown					= 0;
		$at->sum_bonus_exp_fight				= 0;
		$at->sum_bonus_currency_fight			= 0;
		$at->sum_bonus_attribute_training_cost	= 0;
		$at->sum_bonus_training_earn			= 0;
		$at->sum_bonus_training_exp				= 0;
		$at->sum_bonus_quest_time				= 0;
		$at->sum_bonus_food_heal				= 0;
		$at->sum_bonus_npc_in_quests			= 0;
		$at->sum_bonus_daily_npc				= 0;
		$at->sum_bonus_map_npc					= 0;
		$at->sum_bonus_drop						= 0;
		$at->sum_bonus_stamina_max				= 0;
		$at->sum_bonus_stamina_heal				= 0;
		$at->sum_bonus_stamina_consume			= 0;

		$this->_attributes	= $at;

		$attacks	= [];

		if ($anime_id_for_generics) {
			$anime		= Anime::find($anime_id_for_generics);
			$attacks	= array_merge($attacks, $anime->attacks());

			// $this->character_theme = CharacterTheme::find_first('character_id=' . $this->character->id, ['cache' => true]);

			$this->anime	= $anime;
		} else {
			$attacks	= array_merge($attacks, $this->character_theme->attacks());
		}

		if (is_array($theme_ids) && count($theme_ids) > 0) {
			foreach ($theme_ids as $theme_id) {
				$attacks	= array_merge($attacks, CharacterTheme::find($theme_id)->attacks());
			}
		}

		$attacks[]	= new SkipTurnItem();

		foreach($attacks as $attack) {
			if (!is_a($attack, 'SkipTurnItem')) {
				$fake	= new FakePlayerItem($attack->id, $this);
				$attack->set_player($this);
				$attack->set_player_item($fake);
			}

			$this->attacks[]	= $attack;
		}
	}

	function battle_npc() {
		return BattleNPC::find($this->battle_npc_id);
	}

	function attributes() {
		return $this->_attributes;
	}

	function character() {
		return $this->character;
	}

	function anime() {
		return $this->anime;
	}

	function faction() {
		// return Faction::find_first('id = ' . $this->faction_id);
		return $this->faction;
	}

	function character_theme() {
		return $this->character_theme;
	}

	function get_gauge() {
		$this->gauge;
	}

	function set_gauge($value) {
		$this->gauge	= $value;
	}

	function choose_technique($source_technique) {
		// Pode matar o jogador no proximo golpe?
		if ($kill_item = $this->ai_can_kill_in_next_hit($source_technique)) {
			return $kill_item;
		}

		// O NPC vai morrer?
		if ($this->ai_will_die($source_technique)) {
			// Compensa se curar?
			if ($heal_item = $this->ai_worth_heal($source_technique)) {
				return $heal_item;
			}

			// Escolhe um golpe aleatório
			return $this->ai_random_technique();
		} else {
			// Pode defender este golpe?
			if ($defense_item = $this->ai_can_defend_this_hit($source_technique)) {
				return $defense_item;
			}

			return $this->ai_smart_technique();
		}
	}

	private function ai_random_technique() {
		if ($this->has_effects_with('stun')) {
			return new SkipTurnItem();
		}

		$retries	= 0;
		$technique	= null;

		while ($retries++ < 500) {
			$choosen	= $this->attacks[rand(0, sizeof($this->attacks) - 1)];
			if (!$choosen->is_buff) {
				if ($choosen->formula()->consume_mana <= $this->for_mana()) {
					$technique	= $choosen;
					break;
				}
			}
		}

		if(!$technique) {
			// TODO: $_$
			return new SkipTurnItem();
		} else {
			return $technique;
		}
	}

	private function ai_smart_technique() {
		if ($this->has_effects_with('stun')) {
			return new SkipTurnItem();
		}

		return $this->ai_random_technique();
	}

	private function ai_can_kill_in_next_hit($technique) {
		$formule = $technique->formula(true);
		// echo $this->for_life();
		// print_r($this->attacks[0]->formula(true));
		return FALSE;
	}

	private function ai_can_defend_this_hit($technique) {
		return FALSE;
	}

	private function ai_will_die($technique) {
		$formule = $technique->formula(true);
		// if ($formule->demage >= $this->for_life()) {
		// 	return TRUE;
		// }
		return FALSE;
	}

	private function ai_worth_heal($technique) {
		return FALSE;
	}

	function profile_image($path_only = false) {
		if ($this->specific_id) {
			$path	= "/images/adventure/battle/" . $this->specific_id . ".jpg";

			if ($path_only) {
				return $path;
			} else {
				return '<img src="' . asset_url($path) . '" />';
			}
		} else {
			if($path_only) {
				return $this->theme_image->profile_image($path_only);
			} else {
				return $this->theme_image->profile_image();
			}
		}
	}

	function build_technique_lock_uid() {
		return 'NPC_LOCKS_' . $this->uid;
	}

	function build_effects_uid() {
		return 'NPC_EFFECTS_' . $this->uid;
	}

	function build_ability_lock_uid() {
		return 'NPC_ABILITY_LOCK_' . $this->uid;
	}

	function build_speciality_lock_uid() {
		return 'NPC_SPECIALITY_LOCK_' . $this->uid;
	}

	function battle_exp($win = false) {
		if ($win) {
			$exp	= (250 - ($this->level * 5) + $this->level);
		} else {
			$exp	= (200 - ($this->level * 5) + $this->level);
		}

		if ($exp < 0) {
			$exp = 0;
		}

		return floor($exp * EXP_RATE);
	}

	function battle_currency($win = false) {
		if ($win) {
			$currency	= (10 + ($this->level * 4) + 1);
		} else {
			$currency	= (5 + ($this->level * 4) + 1);
		}

		return floor($currency * MONEY_RATE);
	}

	function get_technique($id) {
		foreach ($this->attacks as $key => $value) {
			if ($value->id == $id) {
				return $value;
			}
		}

		return false;
	}

	function get_techniques() {
		$return	= [];
		foreach ($this->attacks as $attack) {
			if (!is_a($attack, 'SkipTurnItem')) {
				$return[]	= $attack->player_item();
			}
		}

		return $return;
	}

	function get_active_pet() {
		if (!$this->_pet_id) {
			return false;
		}

		$attack	= new Item($this->_pet_id);
		$fake	= new FakePlayerItem($this->_pet_id, $this);
		$attack->set_player($this);
		$attack->set_player_item($fake);

		return $fake;
	}

	function get_npc() {
		return Player::get_instance();
	}

	function refresh_talents() {

	}
}
