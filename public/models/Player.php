<?php
class Player extends Relation {
	use				BattleTechniqueLocks;
	use				EffectManager;
	use				AttributeManager;

	static			$paranoid					= true;
	private static	$instance					= null;
	private static	$has_item_cache				= [];
	private			$_attributes				= null;
	private			$training_base				= 300;
	private			$training_day_multipliers	= array(
		1	=> 6,
		2	=> 0,
		3	=> 1,
		4	=> 2,
		5	=> 3,
		6	=> 4,
		7	=> 5
	);

	protected function before_create() {
		$this->last_healed_at	= date('Y-m-d H:i:s');
	}

	protected function after_create() {
		$quest_counters = new PlayerQuestCounter();
		$quest_counters->player_id = $this->id;
		$quest_counters->save();

		$player_tutorial = new PlayerTutorial();
		$player_tutorial->player_id = $this->id;
		$player_tutorial->save();

		$battle_counters = new PlayerBattleCounter();
		$battle_counters->player_id	= $this->id;
		$battle_counters->save();

		$stats = new PlayerStat();
		$stats->player_id	= $this->id;
		$stats->save();

		$battle_stats 							= new PlayerBattleStat();
		$battle_stats->player_id				= $this->id;
		$battle_stats->name						= $this->name;
		$battle_stats->user_id					= $this->user_id;
		$battle_stats->character_id 			= $this->character_id;
		$battle_stats->character_theme_id 		= $this->character_theme_id;
		$battle_stats->faction_id 				= $this->faction_id;
		$battle_stats->graduation_id 			= Graduation::find_first('1=1', ['cache' => true])->id;
		$battle_stats->anime_id 				= $this->character()->anime_id;
		$battle_stats->save();

		$attributes = new PlayerAttribute();
		$attributes->player_id	= $this->id;
		$attributes->save();

		$position = new PlayerPosition();
		$position->player_id = $this->id;
		$position->save();

		$this->character_ability_id		= CharacterAbility::find_first('character_id=' . $this->character_id . ' AND is_initial=1', ['cache' => true])->id;
		$this->character_speciality_id	= CharacterSpeciality::find_first('character_id=' . $this->character_id . ' AND is_initial=1', ['cache' => true])->id;

		$this->graduation_id	= Graduation::find_first('1=1', ['cache' => true])->id;
		$this->save();
	}

	protected function after_destroy() {
		$this->removed_at	= date('Y-m-d H:i:s');
		$this->save();
	}

	protected function after_assign() {
		if ($this->exp < 0) {
			$this->exp = 0;
			$this->save();
		}
		if (!$this->stats()) {
			$stats				= new PlayerStat();
			$stats->player_id	= $this->id;
			$stats->save();
		}

		if (!$this->attributes()) {
			$attributes				= new PlayerAttribute();
			$attributes->player_id	= $this->id;
			$attributes->save();
		}

		if (!$this->_attributes) {
			$this->_attributes	=& $this->attributes();
		}

		$this->clear_fixed_effects('fixed');
		$this->refresh_talents();
	}

	protected function before_update() {
		if($this->less_life > $this->for_life(true)) {
			$this->less_life	= $this->for_life(true);
		}

		if($this->less_mana > $this->for_mana(true)) {
			$this->less_mana	= $this->for_mana(true);
		}

		if ($this->level_screen_seen) {
			if ($this->is_next_level()) {
				// while ($this->is_next_level()) {
					$this->level		+= 1;
					$this->exp			-= $this->level_exp();

					$this->less_mana	= 0;
					$this->less_life	= 0;
					$this->less_stamina	= 0;

					// Checa a conquista de level do player
					$this->achievement_check('level_player');
					// Checa a conquista de level do player
					$this->check_objectives('level_player');
				// }
			}
		}
	}
	
	function welcome_message() {
		# Welcome Message
		$message			= new PrivateMessage();
		$message->to_id		= $this->id;
		$message->subject	= t('welcome_message.subject');
		$message->content	= t('welcome_message.message', [
			'player'	=> $this->name,
			'link'		=> make_url('support'),
			'game'		=> GAME_NAME
		]);
		$message->save();
	}

	function first_login() {
		# Add default character thechniques for this player
		$slot_id	= 0;
		$techniques		= Item::find('is_initial = 1 and item_type_id = 1', [
			'reorder'	=> 'id asc',
			'limit'		=> '10'
		]);
		foreach ($techniques as $technique) {
			$insert				= new PlayerItem();
			$insert->player_id	= $this->id;
			$insert->item_id	= $technique->id;
			$insert->equipped	= 1;
			$insert->slot_id	= $slot_id;
			$insert->save();

			++$slot_id;
		}

		# Add initial currency and update player
		$this->currency			= INITIAL_MONEY;
		$this->first_actions	= 1;
		$this->save();

		# Send welcome message to player
		$this->welcome_message();

		# If need, ass round objectives to user account
		$user	= User::find_first('id=' . $this->user_id);
		if (!$user->objectives) {
			$objectives = Achievement::find("type = 'objectives'", [
				'reorder'	=> 'RAND()',
				'limit'		=> 10
			]);
			foreach ($objectives as $objective) {
				$insert	= new UserObjective();
				$insert->user_id		= $user->id;
				$insert->objective_id	= $objective->id;
				$insert->save();
			}

			$user->objectives = 1;
			$user->save();
		}
	}

	function check_objectives($arch_type = NULL){
		switch($arch_type) {
			case "level_player":
				$achievements = Achievement::find("level_player > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($this->level >= $achievement->level_player ){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b>";
							$pm->save();
						}
					}
				}
				break;
			case "level_account":
				$achievements = Achievement::find("level_account > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					$user = User::find_first("id=".$this->user_id);
					if($user_objective){
						if($user->level >= $achievement->level_account ){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "tutorial":
				$achievements = Achievement::find("tutorial > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_stat = PlayerStat::find_first("player_id=". $this->id);
						if($player_stat->tutorial){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "map":
				$achievements = Achievement::find("map > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($achievement->anime_id && $achievement->map==1){
							$player_map_anime = PlayerMapLog::find("player_id=". $this->id." AND anime_id=".$achievement->anime_id);
							if(sizeof($player_map_anime) == $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}else if($achievement->anime_id && $achievement->map==2 ){
							$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE anime_id=".$achievement->anime_id." and player_id=".$this->id)->result_array();
							if($player_map_anime[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}


						}else if(!$achievement->anime_id && $achievement->map==2){
							$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE player_id=".$this->id)->result_array();
							if($player_map_anime[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "credits":
				$achievements = Achievement::find("credits > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$user = User::find_first("id=".$this->user_id);
						if($user->credits >= $achievement->quantity ){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "currency":
				$achievements = Achievement::find("currency > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($this->currency >= $achievement->quantity ){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "pets":
				$achievements = Achievement::find("pets > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						// Só quer saber a quantidade de pets
						if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && !$achievement->happiness){
							if(sizeof($this->your_pets_achievement()) >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
							// Quer saber um pet especifico
						}else if($achievement->item_id && !$achievement->happiness && !$achievement->quantity && !$achievement->rarity){
							if(sizeof($this->your_pets_achievement(NULL, NULL, $achievement->item_id))){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
							// Quer saber a quantidade de pets por raridade
						}else if($achievement->quantity && !$achievement->item_id && $achievement->rarity && !$achievement->happiness){
							if(sizeof($this->your_pets_achievement($achievement->rarity)) >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}else if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && $achievement->happiness){
							if(sizeof($this->your_pets_achievement(NULL, $achievement->happiness)) >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "battle_npc":
				$achievements = Achievement::find("battle_npc > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						// Só quer saber a quantidade de npcs
						if($achievement->battle_npc && !$achievement->anime_id && !$achievement->character_id){
							if($this->wins_npc >= $achievement->quantity ){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "battle_pvp":
				$achievements = Achievement::find("battle_pvp > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$can = false;
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){

						// Só quer saber a quantidade de pvps
						if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){

							if($this->wins_pvp >= $achievement->quantity ){
								$can = true;
							}
							// Quer saber a quantidade de pvps com determinada facção
						}else if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && $achievement->faction_id){

							$user_objective_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND faction_id=".$achievement->faction_id)->result_array();

							if($user_objective_stats[0]['total'] >= $achievement->quantity ){

								$can = true;
							}
						}else if($achievement->battle_pvp && $achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){
							$user_objective_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND anime_id=".$achievement->anime_id)->result_array();

							if($user_objective_stats[0]['total'] >= $achievement->quantity ){
								$can = true;
							}
						}else if($achievement->battle_pvp && !$achievement->anime_id && $achievement->character_id && !$achievement->faction_id){
							$user_objective_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND character_id=".$achievement->character_id)->result_array();

							if($user_objective_stats[0]['total'] >= $achievement->quantity ){
								$can = true;
							}
						}
						if($can){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "history_mode":
				$achievements = Achievement::find("history_mode > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$user_history_mode_subgroup = UserHistoryModeSubgroup::find_first("history_mode_subgroup_id=".$achievement->history_mode." AND user_id=".$this->user_id." AND complete=1");
						if($user_history_mode_subgroup){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "challenges":
				$achievements = Achievement::find("challenges > 0 and type = 'objectives'");
				if ($_SESSION['universal']) {
					echo '<pre>';
					print_r($user_objective);
					echo '</pre>';
				}
				foreach ($achievements as $achievement) {
					$user_objective = UserObjective::find_first("objective_id = {$achievement->id} and user_id = {$this->user_id} and complete = 0");
					if ($_SESSION['universal']) {
						echo '<pre>';
						print_r($user_objective);
						echo '</pre>';
					}
					if ($user_objective) {
						$player_challenge = PlayerChallenge::find_first("challenge_id = {$achievement->challenges} and player_id = ".$this->id, [
							'order'	=> 'quantity desc'
						]);

						if ($_SESSION['universal']) {
							echo '<pre>';
							print_r($player_challenge);
							echo '</pre>';
						}
						if ($player_challenge) {
							if ($player_challenge->quantity >= $achievement->challenges_floor) {
								if ($_SESSION['universal']) { echo 'cheguei aqui'; }
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								// Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: {$achievement->description()->name}";
								$pm->content	= "Você completou o Objetivo de Round: <b>{$achievement->description()->name}</b>";
								$pm->save();
							}
						}
					}
				}
				break;
			case "organization":
				$achievements = Achievement::find("organization > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($this->organization_id){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "treasure":
				$achievements = Achievement::find("treasure > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($this->treasure_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "friends":
				$achievements = Achievement::find("friends > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($achievement->friends && !$achievement->friends_send_gifts && !$achievement->friends_received_gifts){
							$player_friends = Recordset::query("select count(id) as total from player_friend_lists WHERE  player_id=".$this->id)->result_array();
							if($player_friends[0]['total'] >= $achievement->friends){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}else if($achievement->friends && $achievement->friends_send_gifts && !$achievement->friends_received_gifts){
							$player_send_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  player_id=".$this->id)->result_array();
							if($player_send_gifts[0]['total'] >= $achievement->friends_send_gifts){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}else if($achievement->friends && !$achievement->friends_send_gifts && $achievement->friends_received_gifts){
							$player_receveid_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  friend_id=".$this->id)->result_array();
							if($player_receveid_gifts[0]['total'] >= $achievement->friends_received_gifts){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "character":
				$achievements = Achievement::find("achievements.character > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$user_character = UserCharacter::find_first("user_id=". $this->user_id." AND character_id=".$achievement->character);
						if($user_character){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "character_theme":
				$achievements = Achievement::find("character_theme > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$user_character_theme = UserCharacterTheme::find_first("user_id=". $this->user_id." AND character_theme_id=".$achievement->character_theme);
						if($user_character_theme){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "luck":
				$achievements = Achievement::find("luck > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_luck_log = PlayerLuckLog::find_first("player_id=". $this->id." AND luck_reward_id=".$achievement->luck);
						if($player_luck_log){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "fragments":
				$achievements = Achievement::find("fragments > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_fragments = PlayerItem::find_first("player_id=". $this->id." AND item_id=446");
						if($player_fragments){
							if($achievement->fragments==1){
								if($player_fragments->quantity >= $achievement->quantity){
									$user_objective->complete = 1;
									$user_objective->completed_at = now(true);
									$user_objective->save();

									//Recompensa
									$user	= User::get_instance();
									$user->round_points(1);

									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Objetivo: ". $achievement->description()->name;
									$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
									$pm->save();
								}
							}
						}
						if($achievement->fragments==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->fragments >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "wanted":
				$achievements = Achievement::find("wanted > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($achievement->wanted==1){
							$player_wanted = Recordset::query("select count(id) as total from player_wanteds WHERE enemy_id=".$this->id)->result_array();
							if($player_wanted[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
						if($achievement->wanted==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($this->won_last_battle >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "sands":
				$achievements = Achievement::find("sands > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_sands = PlayerItem::find_first("player_id=". $this->id." AND item_id=1719");
						if($player_sands){
							if($achievement->sands==1){
								if($player_sands->quantity >= $achievement->quantity){
									$user_objective->complete = 1;
									$user_objective->completed_at = now(true);
									$user_objective->save();

									//Recompensa
									$user	= User::get_instance();
									$user->round_points(1);

									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Objetivo: ". $achievement->description()->name;
									$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
									$pm->save();
								}
							}
						}
						if($achievement->sands==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->sands >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "bloods":
				$achievements = Achievement::find("bloods > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_bloods = PlayerItem::find_first("player_id=". $this->id." AND item_id=1720");
						if($player_bloods){
							if($achievement->bloods==1){
								if($player_bloods->quantity >= $achievement->quantity){
									$user_objective->complete = 1;
									$user_objective->completed_at = now(true);
									$user_objective->save();

									//Recompensa
									$user	= User::get_instance();
									$user->round_points(1);

									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Objetivo: ". $achievement->description()->name;
									$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
									$pm->save();
								}
							}
						}
						if($achievement->bloods==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->bloods >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}
					}
				}
				break;
			case "equipment":
				$achievements = Achievement::find("equipment > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						if($achievement->equipment==1 && $achievement->rarity){
							$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$this->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."'")->result_array();
							if($player_equipments[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}elseif($achievement->equipment==1 && !$achievement->rarity){
							$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$this->id." AND item_id in (select id from items WHERE item_type_id=8)")->result_array();
							if($player_equipments[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}else{
							$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$this->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."' AND equipped=1")->result_array();
							if($player_equipments[0]['total'] >= $achievement->quantity){
								$user_objective->complete = 1;
								$user_objective->completed_at = now(true);
								$user_objective->save();

								//Recompensa
								$user	= User::get_instance();
								$user->round_points(1);

								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Objetivo: ". $achievement->description()->name;
								$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
								$pm->save();
							}
						}

					}
				}
				break;
			case "grimoire":
				$achievements = Achievement::find("grimoire > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_grimoire = PlayerItem::find_first("player_id=". $this->id." AND item_id=".$achievement->item_id);
						if($player_grimoire){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "time_quests":
				$achievements = Achievement::find("time_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->time_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "battle_quests":
				$achievements = Achievement::find("battle_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->combat_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "pvp_quests":
				$achievements = Achievement::find("pvp_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->pvp_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "daily_quests":
				$achievements = Achievement::find("daily_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->daily_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "account_quests":
				$achievements = Achievement::find("account_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = UserQuestCounter::find_first("user_id=". $this->user_id);
						if($player_quest->daily_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "pet_quests":
				$achievements = Achievement::find("pet_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->pet_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
			case "weekly_quests":
				$achievements = Achievement::find("weekly_quests > 0 AND type='objectives'");
				foreach($achievements as $achievement){
					$user_objective = UserObjective::find_first("objective_id=".$achievement->id." AND user_id=".$this->user_id." AND complete=0");
					if($user_objective){
						$organization_quest = OrganizationQuestCounter::find_first("organization_id=". $this->organization_id);
						if($organization_quest->daily_total >= $achievement->quantity){
							$user_objective->complete = 1;
							$user_objective->completed_at = now(true);
							$user_objective->save();

							//Recompensa
							$user	= User::get_instance();
							$user->round_points(1);

							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Objetivo: ". $achievement->description()->name;
							$pm->content	= "Você completou o Objetivo de Round: <b>". $achievement->description()->name ."</b> ";
							$pm->save();
						}
					}
				}
				break;
		}
	}
	function achievement_check($arch_type = NULL){
		switch($arch_type) {
			case "level_player":
				$achievements = Achievement::find("level_player > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($this->level >= $achievement->level_player ){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();

							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}

				}
				break;
			case "level_account":
				$achievements = Achievement::find("level_account > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$user = User::find_first("id=".$this->user_id);
						if($user->level >= $achievement->level_account ){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "tutorial":
				$achievements = Achievement::find("tutorial > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_stat = PlayerStat::find_first("player_id=". $this->id);
						if($player_stat->tutorial){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();

							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->equipment){
									$reward .= "Você ganhou 1 Equipamento Comum<br />";

									Item::generate_equipment($this, 0);
								}
								if($rewards->pet){
									$reward .= "Você ganhou 1 Mascote Comum Aleatório<br />";
									// Dá um pet random!
									$npc_pet	= Item::find_first('item_type_id=3 AND is_initial=1 AND rarity="common"', ['reorder' => 'RAND()']);

									$player_pet						= new PlayerItem();
									$player_pet->item_id			= $npc_pet->id;
									$player_pet->player_id			= $this->id;
									$player_pet->save();
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "map":
				$achievements = Achievement::find("map > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($achievement->anime_id && $achievement->map==1){
							$player_map_anime = PlayerMapLog::find("player_id=". $this->id." AND anime_id=".$achievement->anime_id);
							if(sizeof($player_map_anime) == $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}else if($achievement->anime_id && $achievement->map==2 ){
							$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE anime_id=".$achievement->anime_id." and player_id=".$this->id)->result_array();
							if($player_map_anime[0]['total'] >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}


						}else if(!$achievement->anime_id && $achievement->map==2){
							$player_map_anime = Recordset::query("select sum(quantity) as total from player_map_logs WHERE player_id=".$this->id)->result_array();
							if($player_map_anime[0]['total'] >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "credits":
				$achievements = Achievement::find("credits > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$user = User::find_first("id=".$this->user_id);
						if($user->credits >= $achievement->quantity ){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "currency":
				$achievements = Achievement::find("currency > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($this->currency >= $achievement->quantity ){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "pets":
				$achievements = Achievement::find("pets > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						// Só quer saber a quantidade de pets
						if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && !$achievement->happiness){
							if(sizeof($this->your_pets_achievement()) >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
							// Quer saber um pet especifico
						}else if($achievement->item_id && !$achievement->happiness && !$achievement->quantity && !$achievement->rarity){
							if(sizeof($this->your_pets_achievement(NULL, NULL, $achievement->item_id))){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
							// Quer saber a quantidade de pets por raridade
						}else if($achievement->quantity && !$achievement->item_id && $achievement->rarity && !$achievement->happiness){
							if(sizeof($this->your_pets_achievement($achievement->rarity)) >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}else if($achievement->quantity && !$achievement->item_id && !$achievement->rarity && $achievement->happiness){
							if(sizeof($this->your_pets_achievement(NULL, $achievement->happiness)) >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "battle_npc":
				$achievements = Achievement::find("battle_npc > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						// Só quer saber a quantidade de npcs
						if($achievement->battle_npc && !$achievement->anime_id && !$achievement->character_id){
							if($this->wins_npc >= $achievement->quantity ){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "battle_pvp":
				$achievements = Achievement::find("battle_pvp > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$can = false;
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){

						// Só quer saber a quantidade de pvps
						if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){

							if($this->wins_pvp >= $achievement->quantity ){
								$can = true;
							}
							// Quer saber a quantidade de pvps com determinada facção
						}else if($achievement->battle_pvp && !$achievement->anime_id && !$achievement->character_id && $achievement->faction_id){

							$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND faction_id=".$achievement->faction_id)->result_array();

							if($player_achievement_stats[0]['total'] >= $achievement->quantity ){

								$can = true;
							}
						}else if($achievement->battle_pvp && $achievement->anime_id && !$achievement->character_id && !$achievement->faction_id){
							$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND anime_id=".$achievement->anime_id)->result_array();

							if($player_achievement_stats[0]['total'] >= $achievement->quantity ){
								$can = true;
							}
						}else if($achievement->battle_pvp && !$achievement->anime_id && $achievement->character_id && !$achievement->faction_id){
							$player_achievement_stats = Recordset::query("select sum(quantity) as total from player_achievement_stats WHERE player_id=".$this->id." AND character_id=".$achievement->character_id)->result_array();

							if($player_achievement_stats[0]['total'] >= $achievement->quantity ){
								$can = true;
							}
						}
						if($can){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "history_mode":
				$achievements = Achievement::find("history_mode > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$user_history_mode_subgroup = UserHistoryModeSubgroup::find_first("history_mode_subgroup_id=".$achievement->history_mode." AND user_id=".$this->user_id." AND complete=1");
						if($user_history_mode_subgroup){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "challenges":
				$achievements = Achievement::find("challenges > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_challenge = PlayerChallenge::find_first("challenge_id=".$achievement->challenges." AND player_id=".$this->id ." ORDER BY quantity desc");
						if($player_challenge){
							if($player_challenge->quantity > $achievement->challenges_floor){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();

								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "organization":
				$achievements = Achievement::find("organization > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($this->organization_id){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "treasure":
				$achievements = Achievement::find("treasure > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($this->treasure_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "friends":
				$achievements = Achievement::find("friends > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($achievement->friends && !$achievement->friends_send_gifts && !$achievement->friends_received_gifts){
							$player_friends = Recordset::query("select count(id) as total from player_friend_lists WHERE  player_id=".$this->id)->result_array();
							if($player_friends[0]['total'] >= $achievement->friends){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}else if($achievement->friends && $achievement->friends_send_gifts && !$achievement->friends_received_gifts){
							$player_send_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  player_id=".$this->id)->result_array();
							if($player_send_gifts[0]['total'] >= $achievement->friends_send_gifts){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}else if($achievement->friends && !$achievement->friends_send_gifts && $achievement->friends_received_gifts){
							$player_receveid_gifts = Recordset::query("select count(id) as total from player_gift_logs WHERE  friend_id=".$this->id)->result_array();
							if($player_receveid_gifts[0]['total'] >= $achievement->friends_received_gifts){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "character":
				$achievements = Achievement::find("achievements.character > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$user_character = UserCharacter::find_first("user_id=". $this->user_id." AND character_id=".$achievement->character);
						if($user_character){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "character_theme":
				$achievements = Achievement::find("character_theme > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$user_character_theme = UserCharacterTheme::find_first("user_id=". $this->user_id." AND character_theme_id=".$achievement->character_theme);
						if($user_character_theme){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "luck":
				$achievements = Achievement::find("luck > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_luck_log = PlayerLuckLog::find_first("player_id=". $this->id." AND luck_reward_id=".$achievement->luck);
						if($player_luck_log){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "fragments":
				$achievements = Achievement::find("fragments > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_fragments = PlayerItem::find_first("player_id=". $this->id." AND item_id=446");
						if($player_fragments){
							if($achievement->fragments==1){
								if($player_fragments->quantity >= $achievement->quantity){
									$new_achievement = new PlayerAchievement();
									$new_achievement->player_id 	 = $this->id;
									$new_achievement->achievement_id = $achievement->id;
									$new_achievement->save();
									//Recompensa
									$rewards = $achievement->achievement_rewards($achievement->id);
									$reward = "";
									if($rewards){
										$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
										if($rewards->exp){
											$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
											//Exp para o Player
											$this->earn_exp($rewards->exp);
										}
										if($rewards->exp_user){
											$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
											//Exp para a conta
											$user	= User::get_instance();
											$user->exp($rewards->exp_user);
										}
										if($rewards->currency){
											$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
											// Dinheiro para o player
											$this->earn($rewards->currency);
										}
										if($rewards->credits){
											$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
											//Crédito para a conta
											$user	= User::get_instance();
											$user->earn($rewards->credits);
										}
										if($rewards->item_id){
											$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
											//Item para o player
											$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

											if(!$player_item_exist){
												$player_item			= new PlayerItem();
												$player_item->item_id	= $rewards->item_id;
												$player_item->quantity	= $rewards->quantity;
												$player_item->player_id	= $this->id;
												$player_item->save();
											}else{
												$player_item_exist->quantity += $rewards->quantity;
												$player_item_exist->save();
											}
										}
										if($rewards->character_theme_id){
											$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
											//Dá o Tema ao player
											$reward_theme						= new UserCharacterTheme();
											$reward_theme->user_id				= $this->user_id;
											$reward_theme->character_theme_id	= $rewards->character_theme_id;
											$reward_theme->was_reward			= 1;
											$reward_theme->save();
										}
										if($rewards->character_id){
											$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
											//Dá o Personagem ao player
											$reward_character					= new UserCharacter();
											$reward_character->user_id			= $this->user_id;
											$reward_character->character_id	= $rewards->character_id;
											$reward_character->was_reward	= 1;
											$reward_character->save();
										}
										if($rewards->headline_id){
											$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
											// Dá o titulo ao player
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $this->user_id;
											$reward_headline->headline_id	= $rewards->headline_id;
											$reward_headline->save();
										}
									}
									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Conquista: ". $achievement->description()->name;
									$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
									$pm->save();
								}
							}
						}
						if($achievement->fragments==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->fragments >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "wanted":
				$achievements = Achievement::find("wanted > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($achievement->wanted==1){
							$player_wanted = Recordset::query("select count(id) as total from player_wanteds WHERE enemy_id=".$this->id)->result_array();
							if($player_wanted[0]['total'] >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
						if($achievement->wanted==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($this->won_last_battle >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "sands":
				$achievements = Achievement::find("sands > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_sands = PlayerItem::find_first("player_id=". $this->id." AND item_id=1719");
						if($player_sands){
							if($achievement->sands==1){
								if($player_sands->quantity >= $achievement->quantity){
									$new_achievement = new PlayerAchievement();
									$new_achievement->player_id 	 = $this->id;
									$new_achievement->achievement_id = $achievement->id;
									$new_achievement->save();
									//Recompensa
									$rewards = $achievement->achievement_rewards($achievement->id);
									$reward = "";
									if($rewards){
										$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
										if($rewards->exp){
											$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
											//Exp para o Player
											$this->earn_exp($rewards->exp);
										}
										if($rewards->exp_user){
											$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
											//Exp para a conta
											$user	= User::get_instance();
											$user->exp($rewards->exp_user);
										}
										if($rewards->currency){
											$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
											// Dinheiro para o player
											$this->earn($rewards->currency);
										}
										if($rewards->credits){
											$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
											//Crédito para a conta
											$user	= User::get_instance();
											$user->earn($rewards->credits);
										}
										if($rewards->item_id){
											$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
											//Item para o player
											$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

											if(!$player_item_exist){
												$player_item			= new PlayerItem();
												$player_item->item_id	= $rewards->item_id;
												$player_item->quantity	= $rewards->quantity;
												$player_item->player_id	= $this->id;
												$player_item->save();
											}else{
												$player_item_exist->quantity += $rewards->quantity;
												$player_item_exist->save();
											}
										}
										if($rewards->character_theme_id){
											$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
											//Dá o Tema ao player
											$reward_theme						= new UserCharacterTheme();
											$reward_theme->user_id				= $this->user_id;
											$reward_theme->character_theme_id	= $rewards->character_theme_id;
											$reward_theme->was_reward			= 1;
											$reward_theme->save();
										}
										if($rewards->character_id){
											$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
											//Dá o Personagem ao player
											$reward_character					= new UserCharacter();
											$reward_character->user_id			= $this->user_id;
											$reward_character->character_id	= $rewards->character_id;
											$reward_character->was_reward	= 1;
											$reward_character->save();
										}
										if($rewards->headline_id){
											$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
											// Dá o titulo ao player
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $this->user_id;
											$reward_headline->headline_id	= $rewards->headline_id;
											$reward_headline->save();
										}
									}
									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Conquista: ". $achievement->description()->name;
									$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
									$pm->save();
								}
							}
						}
						if($achievement->sands==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->sands >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "bloods":
				$achievements = Achievement::find("bloods > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_bloods = PlayerItem::find_first("player_id=". $this->id." AND item_id=1720");
						if($player_bloods){
							if($achievement->bloods==1){
								if($player_bloods->quantity >= $achievement->quantity){
									$new_achievement = new PlayerAchievement();
									$new_achievement->player_id 	 = $this->id;
									$new_achievement->achievement_id = $achievement->id;
									$new_achievement->save();
									//Recompensa
									$rewards = $achievement->achievement_rewards($achievement->id);
									$reward = "";
									if($rewards){
										$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
										if($rewards->exp){
											$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
											//Exp para o Player
											$this->earn_exp($rewards->exp);
										}
										if($rewards->exp_user){
											$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
											//Exp para a conta
											$user	= User::get_instance();
											$user->exp($rewards->exp_user);
										}
										if($rewards->currency){
											$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
											// Dinheiro para o player
											$this->earn($rewards->currency);
										}
										if($rewards->credits){
											$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
											//Crédito para a conta
											$user	= User::get_instance();
											$user->earn($rewards->credits);
										}
										if($rewards->item_id){
											$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
											//Item para o player
											$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

											if(!$player_item_exist){
												$player_item			= new PlayerItem();
												$player_item->item_id	= $rewards->item_id;
												$player_item->quantity	= $rewards->quantity;
												$player_item->player_id	= $this->id;
												$player_item->save();
											}else{
												$player_item_exist->quantity += $rewards->quantity;
												$player_item_exist->save();
											}
										}
										if($rewards->character_theme_id){
											$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
											//Dá o Tema ao player
											$reward_theme						= new UserCharacterTheme();
											$reward_theme->user_id				= $this->user_id;
											$reward_theme->character_theme_id	= $rewards->character_theme_id;
											$reward_theme->was_reward			= 1;
											$reward_theme->save();
										}
										if($rewards->character_id){
											$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
											//Dá o Personagem ao player
											$reward_character					= new UserCharacter();
											$reward_character->user_id			= $this->user_id;
											$reward_character->character_id	= $rewards->character_id;
											$reward_character->was_reward	= 1;
											$reward_character->save();
										}
										if($rewards->headline_id){
											$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
											// Dá o titulo ao player
											$reward_headline				= new UserHeadline();
											$reward_headline->user_id		= $this->user_id;
											$reward_headline->headline_id	= $rewards->headline_id;
											$reward_headline->save();
										}
									}
									// Envia uma mensagem para o jogador avisando do prêmio
									$pm				= new PrivateMessage();
									$pm->to_id		= $this->id;
									$pm->subject	= "Conquista: ". $achievement->description()->name;
									$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
									$pm->save();
								}
							}
						}
						if($achievement->bloods==2){
							$player_change = PlayerStat::find_first("player_id=".$this->id);
							if($player_change->bloods >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}
					}
				}
				break;
			case "equipment":
				$achievements = Achievement::find("equipment > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						if($achievement->equipment==1){
							$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$this->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."'")->result_array();
							if($player_equipments[0]['total'] >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}else{
							$player_equipments = Recordset::query("select count(id) as total from player_items WHERE player_id=".$this->id." AND item_id in (select id from items WHERE item_type_id=8) AND rarity='".$achievement->rarity."' AND equipped=1")->result_array();
							if($player_equipments[0]['total'] >= $achievement->quantity){
								$new_achievement = new PlayerAchievement();
								$new_achievement->player_id 	 = $this->id;
								$new_achievement->achievement_id = $achievement->id;
								$new_achievement->save();
								//Recompensa
								$rewards = $achievement->achievement_rewards($achievement->id);
								$reward = "";
								if($rewards){
									$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
									if($rewards->exp){
										$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
										//Exp para o Player
										$this->earn_exp($rewards->exp);
									}
									if($rewards->exp_user){
										$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
										//Exp para a conta
										$user	= User::get_instance();
										$user->exp($rewards->exp_user);
									}
									if($rewards->currency){
										$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
										// Dinheiro para o player
										$this->earn($rewards->currency);
									}
									if($rewards->credits){
										$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
										//Crédito para a conta
										$user	= User::get_instance();
										$user->earn($rewards->credits);
									}
									if($rewards->item_id){
										$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
										//Item para o player
										$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

										if(!$player_item_exist){
											$player_item			= new PlayerItem();
											$player_item->item_id	= $rewards->item_id;
											$player_item->quantity	= $rewards->quantity;
											$player_item->player_id	= $this->id;
											$player_item->save();
										}else{
											$player_item_exist->quantity += $rewards->quantity;
											$player_item_exist->save();
										}
									}
									if($rewards->character_theme_id){
										$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
										//Dá o Tema ao player
										$reward_theme						= new UserCharacterTheme();
										$reward_theme->user_id				= $this->user_id;
										$reward_theme->character_theme_id	= $rewards->character_theme_id;
										$reward_theme->was_reward			= 1;
										$reward_theme->save();
									}
									if($rewards->character_id){
										$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
										//Dá o Personagem ao player
										$reward_character					= new UserCharacter();
										$reward_character->user_id			= $this->user_id;
										$reward_character->character_id	= $rewards->character_id;
										$reward_character->was_reward	= 1;
										$reward_character->save();
									}
									if($rewards->headline_id){
										$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
										// Dá o titulo ao player
										$reward_headline				= new UserHeadline();
										$reward_headline->user_id		= $this->user_id;
										$reward_headline->headline_id	= $rewards->headline_id;
										$reward_headline->save();
									}
								}
								// Envia uma mensagem para o jogador avisando do prêmio
								$pm				= new PrivateMessage();
								$pm->to_id		= $this->id;
								$pm->subject	= "Conquista: ". $achievement->description()->name;
								$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
								$pm->save();
							}
						}

					}
				}
				break;
			case "grimoire":
				$achievements = Achievement::find("grimoire > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_grimoire = PlayerItem::find_first("player_id=". $this->id." AND item_id=".$achievement->item_id);
						if($player_grimoire){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "time_quests":
				$achievements = Achievement::find("time_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->time_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "battle_quests":
				$achievements = Achievement::find("battle_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->combat_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "pvp_quests":
				$achievements = Achievement::find("pvp_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->pvp_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "daily_quests":
				$achievements = Achievement::find("daily_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->daily_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "account_quests":
				$achievements = Achievement::find("account_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = UserQuestCounter::find_first("user_id=". $this->user_id);
						if($player_quest->daily_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "pet_quests":
				$achievements = Achievement::find("pet_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$player_quest = PlayerQuestCounter::find_first("player_id=". $this->id);
						if($player_quest->pet_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
			case "weekly_quests":
				$achievements = Achievement::find("weekly_quests > 0 AND type='achievement'");
				foreach($achievements as $achievement){
					$player_achievement = PlayerAchievement::find_first("achievement_id=".$achievement->id." AND player_id=".$this->id);
					if(!$player_achievement){
						$organization_quest = OrganizationQuestCounter::find_first("organization_id=". $this->organization_id);
						if($organization_quest->daily_total >= $achievement->quantity){
							$new_achievement = new PlayerAchievement();
							$new_achievement->player_id 	 = $this->id;
							$new_achievement->achievement_id = $achievement->id;
							$new_achievement->save();
							//Recompensa
							$rewards = $achievement->achievement_rewards($achievement->id);
							$reward = "";
							if($rewards){
								$reward .= "e ganhou as seguintes recompensas: <br /><br/>";
								if($rewards->exp){
									$reward .= $rewards->exp ." ". t('ranked.exp') ."<br />";
									//Exp para o Player
									$this->earn_exp($rewards->exp);
								}
								if($rewards->exp_user){
									$reward .= $rewards->exp_user ." ". t('ranked.exp_account') ."<br />";
									//Exp para a conta
									$user	= User::get_instance();
									$user->exp($rewards->exp_user);
								}
								if($rewards->currency){
									$reward .= $rewards->currency ." ". t('currencies.' . $this->character()->anime_id) ."<br />";
									// Dinheiro para o player
									$this->earn($rewards->currency);
								}
								if($rewards->credits){
									$reward .= $rewards->credits ." ". t('treasure.show.credits') ."<br />";
									//Crédito para a conta
									$user	= User::get_instance();
									$user->earn($rewards->credits);
								}
								if($rewards->item_id){
									$reward .= $rewards->quantity ."x ". Item::find($rewards->item_id)->description()->name ."<br />";
									//Item para o player
									$player_item_exist	= PlayerItem::find_first("item_id=".$rewards->item_id." AND player_id=". $this->id);

									if(!$player_item_exist){
										$player_item			= new PlayerItem();
										$player_item->item_id	= $rewards->item_id;
										$player_item->quantity	= $rewards->quantity;
										$player_item->player_id	= $this->id;
										$player_item->save();
									}else{
										$player_item_exist->quantity += $rewards->quantity;
										$player_item_exist->save();
									}
								}
								if($rewards->character_theme_id){
									$reward .= t('treasure.show.theme') ." ". CharacterTheme::find($rewards->character_theme_id)->description()->name ."<br />";
									//Dá o Tema ao player
									$reward_theme						= new UserCharacterTheme();
									$reward_theme->user_id				= $this->user_id;
									$reward_theme->character_theme_id	= $rewards->character_theme_id;
									$reward_theme->was_reward			= 1;
									$reward_theme->save();
								}
								if($rewards->character_id){
									$reward .= t('treasure.show.character') ." ". Character::find($rewards->character_id)->description()->name ."<br />";
									//Dá o Personagem ao player
									$reward_character					= new UserCharacter();
									$reward_character->user_id			= $this->user_id;
									$reward_character->character_id	= $rewards->character_id;
									$reward_character->was_reward	= 1;
									$reward_character->save();
								}
								if($rewards->headline_id){
									$reward .= t('treasure.show.headline') ." ". Headline::find($rewards->headline_id)->description()->name ."<br />";
									// Dá o titulo ao player
									$reward_headline				= new UserHeadline();
									$reward_headline->user_id		= $this->user_id;
									$reward_headline->headline_id	= $rewards->headline_id;
									$reward_headline->save();
								}
							}
							// Envia uma mensagem para o jogador avisando do prêmio
							$pm				= new PrivateMessage();
							$pm->to_id		= $this->id;
							$pm->subject	= "Conquista: ". $achievement->description()->name;
							$pm->content	= "Você completou a conquista: <b>". $achievement->description()->name ."</b> ". $reward;
							$pm->save();
						}
					}
				}
				break;
		}
	}

	function at_low_stat() {
		if(($this->for_mana() < $this->for_mana(true) / 2) || ($this->for_life() < $this->for_life(true) / 2)) {
			return true;
		}

		return false;
	}

	function &attributes() {
		if($this->_attributes) {
			return $this->_attributes;
		} else {
			$attributes	= PlayerAttribute::find_first('player_id=' . $this->id);
			return $attributes;
		}
	}

	function battle_npc() {
		return BattleNpc::find($this->battle_npc_id);
	}

	function battle_pvp() {
		$battle	= BattlePvp::find($this->battle_pvp_id);
		if (!$battle) {
			return false;
		}

		$battle->set_player($this->id);

		return $battle;
	}

	function character() {
		return Character::find($this->character_id, array('cache' => true));
	}

	function character_theme($theme_id = NULL) {
		return CharacterTheme::find($theme_id ? $theme_id : $this->character_theme_id, array('cache' => true));
	}

	function character_theme_image() {
		return CharacterThemeImage::find($this->character_theme_image_id, array('cache' => true));
	}

	function map() {
		return Map::find($this->map_id, array('cache' => true));
	}

	function user() {
		return User::find($this->user_id);
	}

	function graduation() {
		return Graduation::find($this->graduation_id, array('cache' => true));
	}

	function stats() {
		return PlayerStat::find_first('player_id=' . $this->id);
	}

	function position() {
		$position = PlayerPosition::find_first('player_id=' . $this->id);
		if (!$position) {
			$position = new PlayerPosition();
			$position->player_id = $this->id;
			$position->organization_id = $this->organization_id;
			$position->save();
		}

		return $position;
	}

	function small_image($path_only = false) {
		$theme	= $this->character_theme();
		$path	= 'criacao/' . $this->character_id . '/' . $theme->theme_code . '/1.jpg';

		if($path_only) {
			return $path;
		} else {
			return '<img src="' . image_url($path) . '" alt="' . $this->name . '" />';
		}
	}

	function profile_image($path_only = false) {
		return $this->character_theme_image()->profile_image($path_only);
	}

	function quest_counters() {
		return PlayerQuestCounter::find_first('player_id=' . $this->id);
	}
	function organization_quest_counters() {
		return OrganizationQuestCounter::find_first('organization_id=' . $this->organization_id);
	}

	function battle_counters() {
		return PlayerBattleCounter::find_first('player_id=' . $this->id);
	}

	function for_atk_trained() { return $this->for_atk; }
	function for_def_trained() { return $this->for_def; }
	function for_crit_trained() { return $this->for_crit; }
	function for_abs_trained() { return $this->for_abs; }
	function for_prec_trained() { return $this->for_prec; }
	function for_init_trained() { return $this->for_init; }
	function for_inc_crit_trained() { return $this->for_inc_crit; }
	function for_inc_abs_trained() { return $this->for_inc_abs; }

	function level_exp() {
		return (250 + $this->level / 5 * 100) * $this->level;
	}

	function is_next_level() {
		return $this->exp >= $this->level_exp();
	}

	function spend($amount) {
		$this->currency	-= $amount;
		$this->save();
	}

	function earn($amount) {
		$this->currency	+= $amount;
		$this->save();

		// Checa o dinheiro do player
		$this->achievement_check(7);
		// Checa o dinheiro do player
		$this->check_objectives(7);
	}

	function earn_exp($amount) {
		$this->exp	+= $amount;
		$this->save();
	}

	function has_technique($technique) {
		return PlayerItem::find('removed=0 AND player_id=' . $this->id . ' AND item_id=' . $technique->id) ? true : false;
	}
	function pages($item_id){
		$result		= array();
		$items		= Item::find("item_type_id = 11 AND parent_id = ". $item_id, array('cache' => true));
		$anime_id	= $this->character()->anime_id;

		foreach ($items as $item) {
			$instance	= Item::find($item->id, array('cache' => true));
			$instance->set_anime($anime_id);
			$result[]	= $instance;
		}

		return $result;

	}
	function player_pages($item_id){
		$player_items	= PlayerItem::find("player_id= ".$this->id." AND item_id= ". $item_id);
		if(sizeof($player_items)){
			return true;
		}else{
			return false;
		}
	}
	function player_pages_ok($item_id){
		$items			= Item::find("parent_id = ". $item_id." AND item_type_id=11", array('cache' => true));

		$items_counter  = sizeof($items);
		$player_item_counter = 0;

		foreach($items as $item){
			$player_items	= PlayerItem::find("player_id= ".$this->id." AND item_id= ". $item->id);

			if($player_items){
				$player_item_counter++;
			}
		}

		if($items_counter==$player_item_counter){
			return true;
		}else{
			return false;
		}
	}
	function has_technique_at($slot) {
		$item	= Recordset::query('
				SELECT
					a.id,
					a.parent_id

				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id IN(1)

				WHERE
					a.player_id=' . $this->id . ' AND
					a.slot_id=' . $slot . ' AND
					a.removed=0'
		);

		if (!$item->num_rows) {
			return false;
		}

		return PlayerItem::find_first($item->row()->id);
	}

	function has_ability($ability) {
		return $this->character_ability_id == $ability->id ? true : false;
	}

	function has_speciality($speciality) {
		return $this->character_speciality_id == $speciality->id ? true : false;
	}

	function has_consumable($consumable) {
		return PlayerItem::find('player_id=' . $this->id . ' AND item_id=' . $consumable->id) ? true : false;
	}

	function has_item($item) {
		if(is_numeric($item)) {
			$id	= $item;
		} else {
			if(is_a($item, 'Item')) {
				$id	= $item->id;
			} elseif(is_a($item, 'PlayerItem')) {
				$id	= $item->item_id;
			} else {
				throw new Exception("Invalid argument", 1);
			}
		}

		if(!isset(Player::$has_item_cache[$this->id])) {
			Player::$has_item_cache[$this->id]	= [];
		}

		if(!isset(Player::$has_item_cache[$this->id][$id])) {
			$result	= $this->get_item($id) ? true : false;
			Player::$has_item_cache[$this->id][$id]	= $result;
		} else {
			$result	= Player::$has_item_cache[$this->id][$id];
		}

		return $result;
	}

	function add_technique($technique, $slot) {
		$disabled	= PlayerItem::find_first('removed=1 AND player_id=' . $this->id . ' AND item_id=' . $technique->id);
		$disables	= Recordset::query('
				SELECT
					a.id

				FROM
					player_items a JOIN items b ON a.item_id=b.id AND b.item_type_id=1

				WHERE
					a.player_id=' . $this->id . ' AND a.slot_id=' . $slot
		);

		foreach ($disables->result_array() as $player_item) {
			$disable			= PlayerItem::find($player_item['id']);
			$disable->removed	= 1;
			$disable->save();
		}

		if ($disabled) {
			$disabled->removed	= 0;
			$disabled->slot_id	= $slot;
			$disabled->save();
		} else {
			$item				= new PlayerItem();
			$item->player_id	= $this->id;
			$item->item_id		= $technique->id;
			$item->slot_id		= $slot;

			$item->save();
		}
	}

	function add_talent($talent) {
		$item				= new PlayerItem();
		$item->player_id	= $this->id;
		$item->item_id		= $talent->id;

		$item->save();
	}

	// TODO: Remover depois -->
	function add_ability($ability) {
		$item					= new PlayerItem();
		$item->player_id		= $this->id;
		$item->item_id			= $ability->id;
		$item->variant_type_id	= $this->ability_variant_type_id;

		$item->save();
	}

	function add_speciality($speciality) {
		$item					= new PlayerItem();
		$item->player_id		= $this->id;
		$item->item_id			= $speciality->id;
		$item->variant_type_id	= $this->speciality_variant_type_id;

		$item->save();
	}

	function add_consumable($consumable, $quantity = 1) {
		if($this->has_consumable($consumable)) {
			$item					= $this->get_item($consumable);
			$item->quantity			+= $quantity;
		} else {
			$item					= new PlayerItem();
			$item->player_id		= $this->id;
			$item->item_id			= $consumable->id;
			$item->quantity			= $quantity;
		}

		$item->save();

		return $item;
	}
	// <--

	function equip_equipment($player_item, $slot) {
		// Unequip other items in the same slot -->
		$equippeds	= PlayerItem::find('player_id=' . $this->id . ' AND slot_name="' . $slot . '"');

		foreach ($equippeds as $equipped) {
			$equipped->equipped	= 0;
			$equipped->save();
		}
		// <--

		$player_item->equipped	= 1;
		$player_item->save();

		$this->_update_sum_attributes();
	}

	function use_consumable($consumable) {
		if($this->has_consumable($consumable)) {
			$item			= $this->get_item($consumable);
			$item->quantity	-= 1;
			$item->save();

			if($item->for_file) {
				$this->less_life	-= $item->for_file;

				if($this->less_life < 0) {
					$this->less_life	= 0;
				}
			}

			if($item->for_mana) {
				$this->less_mana	-= $item->for_mana;

				if($this->less_mana < 0) {
					$this->less_mana	= 0;
				}
			}

			if($item->for_stamina) {
				$this->less_stamina	-= $item->for_stamina;

				if($this->less_stamina < 0) {
					$this->less_stamina	= 0;
				}
			}

			$this->save();

			return $item;
		}

		return false;
	}

	function max_attribute_training() {
		$total	= (4000 + (($this->graduation()->sorting <= 2 ? 0 : $this->graduation()->sorting - 2) * 1000));
		$total	+= $total * $this->training_day_multipliers[date('N')];

		return $total;
	}

	function max_technique_training() {
		$total	= (3000 + (($this->graduation()->sorting <= 2 ? 0 : $this->graduation()->sorting - 2) * 1000));
		$total	+= $total * $this->training_day_multipliers[date('N')];

		return $total;
	}

	function available_attribute_training() {
		return $this->max_attribute_training() - $this->training_points_spent;
	}

	function available_training_points() {
		$user = User::get_instance();
		return (($user->level * 1) + $this->training_total_to_point()) - $this->training_points_spent;
	}

	function training_to_next_point($current = false) {
		if(!$current) {
			return ($this->training_total_to_point() + 1) * $this->training_base;
		} else {
			return $this->training_total - $this->training_total_to_point(true);
		}
	}

	function training_total_to_point($return_amount = false) {
		$counter		= 1;
		$amount			= 0;
		$amount_next	= 0;

		if($this->training_total < $this->training_base) {
			if($return_amount) {
				return 0;
			} else {
				return 0;
			}
		}

		while(true) {
			$points			= $counter * $this->training_base;
			$amount			+= $points;

			if($this->training_total < $amount) {
				$amount_next	= $amount - $points;
				break;
			}

			$counter++;
		}

		return $return_amount ? $amount_next : ($counter - 1);
	}

	function get_item($item) {
		if(is_numeric($item)) {
			$id	= $item;
		} else {
			if(is_a($item, 'Item')) {
				$id	= $item->id;
			} elseif(is_a($item, 'PlayerItem')) {
				$id	= $item->item_id;
			} else {
				throw new Exception("Invalid argument", 1);
			}
		}

		return PlayerItem::find_first('player_id=' . $this->id . ' AND item_id=' . $id);
	}
	function happiness($item_id){
		$happiness = PlayerItem::find_first('item_id='.$item_id.' AND player_id='. $this->id);
		if($happiness){
			if($happiness->happiness < 19){
				return '<img src="'.image_url("icons/happiness_0.png").'" />';
			}else if($happiness->happiness >= 20 && $happiness->happiness < 39){
				return '<img src="'.image_url("icons/happiness_20.png").'" />';
			}else if($happiness->happiness >= 40 && $happiness->happiness < 59){
				return '<img src="'.image_url("icons/happiness_40.png").'" />';
			}else if($happiness->happiness >= 60 && $happiness->happiness < 79){
				return '<img src="'.image_url("icons/happiness_60.png").'"  />';
			}else if($happiness->happiness >= 80 && $happiness->happiness < 99){
				return '<img src="'.image_url("icons/happiness_80.png").'" />';
			}else if($happiness->happiness > 99){
				return '<img src="'.image_url("icons/happiness_100.png").'" />';
			}
		}else{
			return '<img src="'.image_url("icons/happiness_0.png").'"  />';
		}
	}
	function has_talents(){
		$items			= Item::find_all_by_item_type_id(6);
		$has_talents 	= 0;

		foreach($items as $item){
			if($this->has_item($item->id)){
				$has_talents++;
			}
		}
		return $has_talents;
	}
	function happiness_int($item_id) {
		$happiness	= PlayerItem::find_first('item_id=' . $item_id . ' AND player_id=' . $this->id);
		if ($happiness) {
			return $happiness;
		} else {
			return 0;
		}
	}
	function quest_pet_calc_success($pet_quest_id){
		$player	= Player::get_instance();
		$quest_pets_npcs = PetQuestNpc::find('pet_quest_id='.$pet_quest_id);
		$player_quests_pets = PlayerPetQuest::find_first('completed = 0 AND pet_quest_id='.$pet_quest_id.' AND player_id='.$player->id);


		if($player_quests_pets->pet_id_1 || $player_quests_pets->pet_id_2 || $player_quests_pets->pet_id_3){

			$counter = 1;
			$success = 0;

			$total_npc = sizeof($quest_pets_npcs);
			$total_npc = 100/$total_npc;

			foreach($quest_pets_npcs as $quest_pet_npc){

				$total_factor = 0;

				if($quest_pet_npc->rarity){
					$total_factor++;
				}
				if($quest_pet_npc->effect_ids){
					$total_factor++;
				}
				if($quest_pet_npc->anime_id){
					$total_factor++;
				}
				if($quest_pet_npc->happiness){
					$total_factor++;
				}

				switch($counter){
					case 1:
						$campo = "pet_id_1";
						break;
					case 2:
						$campo = "pet_id_2";
						break;
					case 3:
						$campo = "pet_id_3";
						break;
				}

				$item = Item::find_first($player_quests_pets->{$campo});
				$player_item = PlayerItem::find_first('item_id = '.$player_quests_pets->{$campo}.' AND player_id='.$player->id);
				if($item){
					//Verificando se tem raridade do pet
					if($quest_pet_npc->rarity){
						switch($quest_pet_npc->rarity){
							case "common":
								$success += $total_npc/$total_factor;
								break;
							case "rare":
								if($item->rarity == "common"){
									$success += ($total_npc/$total_factor)/2;
								}else{
									$success += $total_npc/$total_factor;
								}
								break;
							case "legendary":
								if($item->rarity == "common"){
									$success += ($total_npc/$total_factor)/3;
								}elseif($item->rarity == "rare"){
									$success += ($total_npc/$total_factor)/2;
								}else{
									$success += $total_npc/$total_factor;
								}
								break;
							case "mega":
								if($item->rarity == "common"){
									$success += ($total_npc/$total_factor)/4;
								}elseif($item->rarity == "rare"){
									$success += ($total_npc/$total_factor)/3;
								}elseif($item->rarity == "legendary"){
									$success += ($total_npc/$total_factor)/2;
								}else{
									$success += $total_npc/$total_factor;
								}
								break;

						}

						if($quest_pet_npc->anime_id){
							if($item->description()->anime_id == $quest_pet_npc->anime_id){
								$success += $total_npc/$total_factor;
							}
						}
						if($quest_pet_npc->happiness){
							if($player_item->happiness >= $quest_pet_npc->happiness){
								$success += $total_npc/$total_factor;
							}elseif($player_item->happiness < $quest_pet_npc->happiness){
								$percentual_hapiness  = $player_item->happiness  / $quest_pet_npc->happiness;
								$success += ($total_npc/$total_factor) * $percentual_hapiness;
							}
						}
						if($quest_pet_npc->effect_ids){
							if($item){
								$effect_ids  = explode(',', $quest_pet_npc->effect_ids);
								if(in_array($item->item_effect_ids,$effect_ids)){
									$success += $total_npc/$total_factor;
								}
							}
						}
					}

				}
				$counter++;
			}
			//Salva a chance de sucesso na tabela
			$player_quests_pets->success_percent = round($success);
			$player_quests_pets->save();
			//-->
			return round($success);
		}else{
			return 0;
		}
	}
	function pets() {
		$result	= [];
		$items	= Recordset::query('
			SELECT
				a.id,
				a.happiness
			FROM
				player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=3
			WHERE
				a.removed = 0 AND a.working = 0 AND a.player_id = ' . $this->id
		);
		foreach ($items->result_array() as $item) {
			$result[]	= PlayerItem::find($item['id']);
		}

		return $result;
	}
	function your_pets() {
		$result	= [];
		$items	= Recordset::query('
				SELECT
					a.id,
					a.happiness,
					a.item_id

				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=3

				WHERE
					a.removed=0 AND a.equipped=0 AND a.working=0 AND
					a.player_id=' . $this->id
		);

		foreach($items->result_array() as $item) {
			$result[]	= Item::find_first($item['item_id']);
		}

		return $result;
	}
	function your_pets_achievement($rarity = NULL, $happiness = NULL, $pet_id = NULL) {
		$result	= [];

		$where = "";

		if($pet_id){
			$where = "AND a.item_id = ".$pet_id;
		}
		if($rarity){
			$where = "AND b.rarity = '".$rarity."'";
		}
		if($happiness){
			$where = "AND a.happiness >= ".$happiness;
		}

		$items	= Recordset::query('
				SELECT
					a.id,
					a.happiness,
					a.item_id,
					a.rarity

				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=3

				WHERE
					a.removed=0 '.$where.'
					AND a.player_id=' . $this->id
		);

		foreach($items->result_array() as $item) {
			$result[]	= Item::find_first($item['item_id']);
		}

		return $result;
	}
	function get_active_pet($player = FALSE) {
		if (!$player)
			$player = $this;

		$pet	= FALSE;
		$items	= Recordset::query("
				SELECT
					a.id
				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id = 3
				WHERE
					a.removed = 0 AND
					a.equipped = 1 AND
					a.player_id = {$player->id}"
		);

		if ($items->num_rows) {
			$pet	= PlayerItem::find_first($items->row()->id);
			$pet->set_player($player);
		}

		return $pet;
	}
	function check_pet_level($pet, $equipped = FALSE) {
		$petItem = Item::find_first($pet->item_id);
		if (
			($petItem->rarity == "common"		&& $pet->exp >= 2500) ||
			($petItem->rarity == "rare"			&& $pet->exp >= 7500) ||
			($petItem->rarity == "legendary"	&& $pet->exp >= 20000)
		){
			$expPet		= 0;
			$expName	= '';
			if ($petItem->rarity == "common") {
				$expPet		= $pet->exp - 2500;
				$expName	= "Raro";
			} else if ($petItem->rarity == "rare") {
				$expPet		= $pet->exp - 7500;
				$expName	= "Lendário";
			} else if ($petItem->rarity == "legendary") {
				$expPet		= $pet->exp - 20000;
				$expName	= "Mega";
			}

			# Adiciona o Novo Pet para o jogador
			$itemParent 		= Item::find_first("parent_id=" . $pet->item_id);
			if ($itemParent) {
				$newPet 			= new PlayerItem();
				$newPet->player_id	= $pet->player()->id;
				$newPet->item_id	= $itemParent->id;
				$newPet->happiness	= $pet->happiness;
				$newPet->equipped	= $equipped ? 1 : 0;
				$newPet->exp		= $expPet;
				$newPet->save();

				# Remove o pet anterior
				$pet->removed 		= 1;

				# Manda Mensagem ao jogador avisando sobre a evolução
				$newPM				= new PrivateMessage();
				$newPM->to_id		= $pet->player()->id;
				$newPM->subject		= "Seu Mascote Evoluiu!";
				$newPM->content 	= "Seu Mascote {$petItem->description()->name} evoluiu para raridade {$expName}";
				$newPM->save();
			}

		}
		return $pet->save();
	}

	function learned_techniques() {
		$result	= array();
		$items	= Recordset::query('
				SELECT
					a.id

				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=1

				WHERE
					a.removed=0 AND
					a.player_id=' . $this->id
		);

		foreach($items->result_array() as $item) {
			$result[]	= PlayerItem::find($item['id']);
		}

		return $result;
	}

	function learned_talents() {
		$result	= array();
		$items	= Recordset::query('
				SELECT
					a.id

				FROM
					player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=6

				WHERE
					a.player_id=' . $this->id
		);

		foreach($items->result_array() as $item) {
			$result[]	= PlayerItem::find($item['id']);
		}

		return $result;
	}

	private function _update_sum_attributes() {
		$at		=& $this->attributes();
		$for_atk			= 0;
		$for_def			= 0;
		$for_init			= 0;
		$for_crit			= 0;
		$for_inc_crit		= 0;
		$for_abs			= 0;
		$for_inc_abs		= 0;
		$for_prec			= 0;

		$exp_battle				= 0;
		$currency_battle 		= 0;
		$currency_quest			= 0;
		$exp_quest				= 0;
		$luck_discount			= 0;
		$item_drop_increase		= 0;

		$generic_technique_damage	= 0;
		$unique_technique_damage	= 0;
		$defense_technique_extra	= 0;



		$items	= Recordset::query('
				SELECT
					a.id

				FROM
					player_items a JOIN items b ON b.id=a.item_id

				WHERE
					a.player_id=' . $this->id . ' AND
					b.item_type_id IN(8) AND
					a.equipped=1');

		foreach ($items->result_array() as $item) {
			$attributes	= PlayerItem::find($item['id'])->attributes();

			$for_atk				+= $attributes->for_atk;
			$for_def				+= $attributes->for_def;
			$for_init				+= $attributes->for_init;
			$for_crit				+= $attributes->for_crit;
			$for_inc_crit			+= $attributes->for_inc_crit;
			$for_abs				+= $attributes->for_abs;
			$for_inc_abs			+= $attributes->for_inc_abs;
			$for_prec				+= $attributes->for_prec;

			$exp_battle				+= $attributes->exp_battle;
			$currency_battle		+= $attributes->currency_battle;
			$exp_quest				+= $attributes->exp_quest;
			$currency_quest			+= $attributes->currency_quest;
			$luck_discount			+= $attributes->luck_discount;
			$item_drop_increase		+= $attributes->item_drop_increase;

			$generic_technique_damage		+= $attributes->generic_technique_damage;
			$unique_technique_damage		+= $attributes->unique_technique_damage;
			$defense_technique_extra		+= $attributes->defense_technique_extra;

		}

		$at->sum_for_atk						= $for_atk;
		$at->sum_for_def						= $for_def;
		$at->sum_for_init						= $for_init;
		$at->sum_for_crit						= $for_crit;
		$at->sum_for_inc_crit					= $for_inc_crit;
		$at->sum_for_abs						= $for_abs;
		$at->sum_for_inc_abs					= $for_inc_abs;
		$at->sum_for_prec						= $for_prec;

		$at->exp_battle							= $exp_battle;
		$at->currency_battle					= $currency_battle;
		$at->exp_quest							= $exp_quest;
		$at->currency_quest						= $currency_quest;
		$at->sum_bonus_luck_discount			= $luck_discount;
		$at->sum_bonus_drop						= $item_drop_increase;

		$at->generic_technique_damage			= $generic_technique_damage;
		$at->unique_technique_damage			= $unique_technique_damage;
		$at->defense_technique_extra			= $defense_technique_extra;


		$at->save();

		$this->check_learned_techniques();
	}

	function get_gauge() {

	}

	function set_gauge($value) {

	}

	function get_techniques() {
		// 1 = Techniques | 7 = Weapons
		$allow = [1];
		$items	= Recordset::query('SELECT a.id FROM player_items a JOIN items b ON b.id=a.item_id WHERE a.player_id=' . $this->id . ' AND b.item_type_id IN(' . implode(',', $allow) . ') AND a.removed=0 ORDER BY a.slot_id ASC');
		$return	= [];

		foreach($items->result_array() as $item) {
			$return[]	= PlayerItem::find($item['id']);
		}

//        $return[]	= new FakePlayerItem(112, $this);
//        $return[]	= new FakePlayerItem(113, $this);

		return $return;
	}

	function get_technique($id) {
		if($id == 1722 || $id == 1723 || $id == 113) {
			return new FakePlayerItem($id, $this);
		}
		$item = Item::find_first($id);
		if(!$item->parent_id){
			return PlayerItem::find(Recordset::query('SELECT a.id FROM player_items a JOIN items b ON b.id=a.item_id WHERE a.player_id=' . $this->id . ' AND a.item_id=' . $id)->row()->id);
		}else{
			return PlayerItem::find(Recordset::query('SELECT a.id FROM player_items a JOIN items b ON b.id=a.item_id WHERE a.player_id=' . $this->id . ' AND a.parent_id=' . $id)->row()->id);
		}
	}

	function get_equipments($slot = null, $unequipped = false) {
		return PlayerItem::find('player_id=' . $this->id . ' AND item_id=114' . ($slot ? ' AND slot_name="' . $slot . '"' : '') . ($unequipped ? ' AND equipped=0' : ''));
	}

	function get_equipment_at_slot($slot) {
		return PlayerItem::find_first('player_id=' . $this->id . ' AND item_id=114  AND slot_name="' . $slot . '" AND equipped=1');
	}

	function get_npc() {
		return SharedStore::G('NPC_' . $this->id);
	}

	function save_npc($npc) {
		if (isset($npc->organization_map_object_id) && $npc->organization_map_object_id) {
			$session = OrganizationMapObjectSession::find_first('player_id=0 AND organization_accepted_event_id=' . $this->organization_accepted_event_id . ' AND organization_id=' . $this->organization_id . ' AND organization_map_object_id=' . $npc->organization_map_object_id);

			if ($session) {
				Recordset::query('UPDATE organization_map_object_sessions SET less_life=less_life + ' . $npc->shared_less_life . ' WHERE id=' . $session->id);

				$npc->less_life = Recordset::query('SELECT less_life FROM organization_map_object_sessions WHERE id=' . $session->id)->row()->less_life;
				$npc->shared_less_life = 0;
			}
		}

		SharedStore::S('NPC_' . $this->id, $npc);
	}

	function get_npc_dungeon() {
		return SharedStore::G('NPC_DUNGEON_' . $this->id);
	}
	function save_npc_dungeon($npc) {
		SharedStore::S('NPC_DUNGEON_' . $this->id, $npc);
	}
	function get_npc_challenge() {
		return SharedStore::G('NPC_CHALLENGE_' . $this->id);
	}

	function save_npc_challenge($npc) {
		SharedStore::S('NPC_CHALLENGE_' . $this->id, $npc);
	}

	function build_technique_lock_uid() {
		return 'LOCKS_' . $this->id;
	}

	function build_effects_uid() {
		return 'EFFECTS_' . $this->id;
	}

	function build_ability_lock_uid() {
		return 'ABILITY_LOCK_' . $this->id;
	}

	function build_speciality_lock_uid() {
		return 'SPECIALITY_LOCK_' . $this->id;
	}

	function battle_exp($win = false) {
		if($win) {
			$exp	= (200 + ($this->level * 5) + $this->level) * 2;
		} else {
			$exp	= (150 + ($this->level * 5) + $this->level) * 2;
		}

		return floor($exp * EXP_RATE);
	}

	function battle_currency($win = false) {
		if ($win) {
			$currency	= (20 + ($this->level * 6) + 1) * 2;
		} else {
			$currency	= (10 + ($this->level * 6) + 1) * 2;
		}

		return floor($currency * MONEY_RATE);
	}

	function ranking() {
		return RankingPlayer::find_first('player_id=' . $this->id);
	}
	function ranking_achievement() {
		return RankingAchievement::find_first('player_id=' . $this->id);
	}
	function ranking_organization() {
		return RankingOrganization::find_first('organization_id=' . $this->organization_id);
	}
	function ranking_account() {
		return RankingAccount::find_first('user_id=' . $this->user_id);
	}
	function fight_power() {
		$fight_power = 0;
		$fight_power += ($this->for_init()) * 300;
		$fight_power += ($this->for_atk() + $this->for_prec() + $this->for_def()) * 250;
		$fight_power += ($this->for_crit() + $this->for_abs()) * 200;

		return round($fight_power);
	}
	function daily_quests() {
		return PlayerDailyQuest::find('player_id=' . $this->id);
	}
	function account_quests() {
		return UserDailyQuest::find('user_id=' . $this->user_id.' AND complete = 0');
	}
	function organization_daily_quests() {
		return OrganizationDailyQuest::find('organization_id=' . $this->organization_id);
	}
	function time_quests() {
		return PlayerTimeQuest::find('player_id=' . $this->id);
	}
	function pet_quests() {
		return PlayerPetQuest::find('player_id=' . $this->id.' AND completed=0');
	}
	function pvp_quests() {
		return PlayerPvpQuest::find('player_id=' . $this->id);
	}
	function ranked($league_id = FALSE) {
		if ($league_id) {
			return PlayerRanked::find_last("player_id={$this->id} and league = {$league_id}");
		} else {
			$league		= Ranked::find_first('started = 1 and finished = 0 order by league desc');
			if ($league) {
				$player_ranked	= PlayerRanked::find_last("player_id={$this->id} and league = {$league->league}");
				if (!$player_ranked) {
					$player_ranked				= new PlayerRanked(); 
					$player_ranked->player_id 	= $this->id;
					$player_ranked->rank		= 10;
					$player_ranked->league		= $league->league;
					$player_ranked->save();
				}

				return $player_ranked;
			}
		}
		return FALSE;
	}
	function player_daily_quest() {
		return PlayerDailyQuest::find_first('player_id=' . $this->id . ' AND daily_quest_id=' . $this->daily_quest_id);
	}
	function organization_daily_quest() {
		return OrganizationDailyQuest::find_first('organization_id=' . $this->organization_id . ' AND daily_quest_id=' . $this->daily_quest_id);
	}
	function player_time_quest() {
		return PlayerTimeQuest::find_first('player_id=' . $this->id . ' AND time_quest_id=' . $this->time_quest_id);
	}
	function player_pet_quest() {
		return PlayerPetQuest::find_first('player_id=' . $this->id . ' AND pet_quest_id=' . $this->pet_quest_id);
	}
	function player_pet_quest_wait($pet_quest_id) {
		return PlayerPetQuest::find('player_id=' . $this->id . ' AND finish_at is not null AND completed = 0 AND pet_quest_id ='.$pet_quest_id);
	}
	function player_pvp_quest() {
		return PlayerPvpQuest::find_first('player_id=' . $this->id . ' AND pvp_quest_id=' . $this->pvp_quest_id);
	}
	function anime() {
		return Anime::find($this->anime_id);
	}
	function check_learned_techniques() {
		foreach ($this->learned_techniques() as $technique) {
			extract($technique->item()->has_requirement($this, ['req_for_mana' => 1]));

			if(!$has_requirement) {
				$technique->removed	= true;
				$technique->save();
			}
		}
	}
	function refresh_talents($enemy = null) {
		$talents	= Recordset::query('SELECT a.item_id FROM player_items a JOIN items b ON b.id=a.item_id AND b.item_type_id=6 WHERE player_id=' . $this->id);
		foreach ($talents->result() as $talent) {
			$talent	= Item::find($talent->item_id);

			foreach ($talent->effects() as $key => $effect) {
				$this->add_fixed_effect($talent, 'talent', $effect, 'player');

				if ($enemy) {
					$enemy->add_fixed_effect($talent, 'talent', $effect, 'enemy');
				}
			}
		}
	}
	function has_gems($item_id, $counter) {
		$item_combinations = ItemGem::find_first("parent_id = " . $item_id);
		$item_combinations = explode(",", $item_combinations->combination);
		$item_combinations = explode("-", $item_combinations[$counter - 1]);

		$gems = [];
		foreach ($item_combinations as $combination) {
			if (!array_key_exists($combination, $gems)) {
				$gems[$combination] = 1;
			} else {
				$gems[$combination]++;
			}
		}

		$has_gems	= TRUE;
		foreach ($gems as $gem_id => $need) {
			$player_gems = PlayerItem::find_first('player_id = ' . $this->id . ' and item_id = ' . $gem_id);
			if (!$player_gems || $player_gems->quantity < $need) {
				$has_gems	= FALSE;
				break;
			}
		}

		return $has_gems;
	}
	function valid_gem_combination($item_id, $combination, $ordem){
		$player_item_gem 		= PlayerItemGem::find_first("item_id=".$item_id." AND player_id=".$this->id);
		if ($player_item_gem) {
			switch ($ordem) {
				case 1:
					$array1 = array("0" => $player_item_gem->gem_1, "1" => $player_item_gem->gem_2);
					$result = array_diff_assoc($combination,$array1);

					return $result;
					break;
				case 2:
					$array1 = array("0" => $player_item_gem->gem_1, "1" => $player_item_gem->gem_2, "2" => $player_item_gem->gem_3);
					$result = array_diff_assoc($combination,$array1);

					return $result;
					break;
				case 3:
					$array1 = array("0" => $player_item_gem->gem_1, "1" => $player_item_gem->gem_2, "2" => $player_item_gem->gem_3, "3" => $player_item_gem->gem_4);
					$result = array_diff_assoc($combination,$array1);

					return $result;
					break;
			}
		} else {
			return false;
		}

	}
	function headline() {
		return Headline::find($this->headline_id);
	}
	function limit_by_day($id) {
		return PlayerGiftLog::find('player_id=' . $id . ' AND DATE(created_at) = DATE(NOW())');
	}
	function has_unlocked_item($id) {
		return PlayerItem::find_first('player_id=' . $this->id . ' AND item_id=' . $id);
	}
	function organization() {
		return Organization::find($this->organization_id);
	}
	function player_tutorial(){
		return PlayerTutorial::find_first("player_id=".$this->id);
	}
	function faction() {
		return Faction::find_first($this->faction_id);
	}
	function tutorial() {
		$player_tutorial = PlayerTutorial::find_first("player_id=".$this->id);
		if ($player_tutorial->status && $player_tutorial->equips && $player_tutorial->pets
			&& $player_tutorial->golpes && $player_tutorial->habilidades && $player_tutorial->aprimoramentos
			&& $player_tutorial->escola && $player_tutorial->treinamento && $player_tutorial->mercado
			&& $player_tutorial->missoes_tempo && $player_tutorial->missoes_pvp && $player_tutorial->missoes_diarias
			&& $player_tutorial->missoes_seguidores && $player_tutorial->battle_npc && $player_tutorial->battle_pvp
			&& $player_tutorial->fidelity && $player_tutorial->battle_village && $player_tutorial->bijuus
			&& $player_tutorial->missoes_conta && $player_tutorial->talents
			&& $player_tutorial->objectives && $player_tutorial->battle_ranked) {

			$player_stat = PlayerStat::find_first("player_id=".$this->id);
			$player_stat->tutorial = 1;
			$player_stat->save();

			//Verifica a conquista de areia - Conquista
			$this->achievement_check("tutorial");
			//Verifica a conquista de areia - Conquista

			return TRUE;
		} else {
			return FALSE;
		}
	}

	function check_heal() {
		$effects	=  $this->get_parsed_effects();
        $now		= new DateTime();

        if (!$this->last_healed_at) {
            $this->last_healed_at	= now(true);
            $this->save();

            $last_heal	= $now;
        } else {
            $last_heal	= new DateTime($this->last_healed_at);
        }

		$extras		= $this->attributes();
        $heal_diff	= $now->diff($last_heal);

		$num_runs	= floor((($heal_diff->d * (24 * 60)) + ($heal_diff->h * 60) + $heal_diff->i / 5));
		if ($num_runs) {
			// ($this->less_life > 0 || $this->less_mana > 0 || $this->less_stamina > 0) && 
			if (!$this->battle_npc_id && !$this->battle_pvp_id) {
				// $max_life		= $this->for_life(true);
				// $max_mana		= $this->for_mana(true);

				// $life_heal		= percent(20, $max_life);
				// $mana_heal		= percent(20, $max_mana);
				$stamina_heal	= 2 + $effects['bonus_stamina_heal'];

				// if ($this->hospital) {
				// 	$life_heal	*= 2;
				// 	$mana_heal	*= 2;
				// }

				// $life_heal		+= percent($extras->life_regen, $life_heal);
				// $mana_heal		+= percent($extras->mana_regen, $mana_heal);
				$stamina_heal	+= percent($extras->stamina_regen, $stamina_heal);

				$current_runs	= 0;
				while ($current_runs++ < $num_runs) {
					// if ($this->less_life > 0)		$this->less_life	-= $life_heal;
					// if ($this->less_mana > 0)		$this->less_mana	-= $mana_heal;
					if ($this->less_stamina > 0)	$this->less_stamina	-= $stamina_heal;

					// if ($this->less_life < 0)		$this->less_life	= 0;
					// if ($this->less_mana < 0)		$this->less_mana	= 0;
					if ($this->less_stamina < 0)	$this->less_stamina	= 0;

					// if ($this->less_life == 0 && $this->less_mana == 0) {
					// 	$this->hospital	= 0;
					// }
				}

				$this->last_healed_at	= now(true);
			}
		}

        $this->save();
	}

	function is_online() {
		return is_player_online($this->id);
	}

	function update_online() {
		$this->last_ip			= getIP();
		$this->last_page		= $_SERVER['REQUEST_URI'];
		$this->last_activity	= now();
		$this->save();

		$check = PlayerLogin::find_first("player_id = {$this->id} and ip = '{$this->last_ip}'");
		if (!$check) {
			$insert				= new PlayerLogin();
			$insert->user_id	= $this->user_id;
			$insert->player_id	= $this->id;
			$insert->ip			= $this->last_ip;
			$insert->browser	= json_encode(getBrowser());
			$insert->save();
		}

		$redis = new Redis();
		if ($redis->pconnect(REDIS_SERVER, REDIS_PORT)) {
			$redis->auth(REDIS_PASS);
			$redis->select(0);

			$redis->set('player_' . $this->id . '_online', now(true));
		}
	}

	static function set_instance($player) {
		Player::$instance	= $player;
	}

	static function &get_instance() {
		return Player::$instance;
	}
}