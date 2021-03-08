<?php
	class LuckController extends Controller {
		private	$default_week		= ['1' => false, '2' => false, '3' => false, '4' => false, '5' => false, '6' => false, '7' => false];

		private	$daily_currency		= 2000;
		private	$daily_credits		= 1;
		
		private	$summon_currency	= 7500;
		private	$summon_credits		= 4;

		private	$weekly_currency	= 6000;
		private	$weekly_credits		= 3;

		function __construct() {
			parent::__construct();

			$player					= Player::get_instance();
			$this->daily_currency	-= percent($player->attributes()->sum_bonus_luck_discount, $this->daily_currency);
			$this->weekly_currency	-= percent($player->attributes()->sum_bonus_luck_discount, $this->weekly_currency);
		}

		function index() {
			$player	= Player::get_instance();
			$stats	= $player->stats();

			$this->assign('daily_credits', $this->daily_credits);
			$this->assign('daily_currency', $this->daily_currency);

			$this->assign('weekly_credits', $this->weekly_credits);
			$this->assign('weekly_currency', $this->weekly_currency);

			$this->assign('player', $player);
			$this->assign('week_data', @unserialize($stats->luck_week_data));
			$this->assign('item_type_ids', Recordset::query('select distinct(item_type_id) from luck_rewards WHERE type=1'));
			$this->assign('reward_list', Recordset::query('
				SELECT
					a.*,
					COUNT(b.id) AS total

				FROM
					luck_rewards a LEFT JOIN player_luck_logs b ON b.luck_reward_id=a.id AND b.player_id=' . $player->id . '
				WHERE 
					a.type=1
		
				GROUP BY a.id
			'));
		}
		function summon() {
			$player	= Player::get_instance();
			$stats	= $player->stats();

			$this->assign('summon_credits', $this->summon_credits);
			$this->assign('summon_currency', $this->summon_currency);

			$this->assign('player', $player);
			$this->assign('reward_list', Recordset::query('
				SELECT
					a.*,
					COUNT(b.id) AS total

				FROM
					luck_rewards a LEFT JOIN player_luck_logs b ON b.luck_reward_id=a.id AND b.player_id=' . $player->id . '
				WHERE 
					a.type=2
		
				GROUP BY a.id
			'));
		}

		function roll() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();
			$player					= Player::get_instance();
			$stats					= $player->stats();
			$attributes				= $player->attributes();
			$user					= User::get_instance();
			$week_full				= true;
			$week_data				= @unserialize($stats->luck_week_data);

			if(!is_array($week_data)) {
				$week_data	= $this->default_week;
			}

			if(isset($_POST['type']) && isset($_POST['currency']) && is_numeric($_POST['currency'])) {
				if($_POST['type'] == 'daily') {
					$is_weekly			= false;
					$needed_currency	= $this->daily_currency;
					$needed_credits		= $this->daily_credits;

					if($player->luck_used) {
						$errors[]	= t('luck.errors.already');
					}
				} elseif($_POST['type'] == 'weekly') {
					$is_weekly			= true;
					$needed_currency	= $this->weekly_currency;
					$needed_credits		= $this->weekly_credits;

					foreach($week_data as $day => $used) {
						if(!$used) {
							$week_full	= false;
						}
					}

					if(!$week_full) {
						$errors[]	= t('luck.errors.week_empty');
					}
				}

				if($_POST['currency'] == 1 && $player->currency < $needed_currency) {
					$errors[]	= t('luck.errors.currency', array('currency' => t('currencies.' . $player->character()->anime_id)));
				}

				if($_POST['currency'] != 1 && $user->credits < $needed_credits) {
					$errors[]	= t('luck.errors.currency', array('currency' => t('currencies.credits')));
				}
			}else{
				$errors[]	= "Inválido";
			}


			if(!sizeof($errors)) {
				$items			= Recordset::query('select group_concat(item_id) as ids from player_items WHERE item_id in (select item_id from luck_rewards WHERE item_type_id=6) AND player_id='. $player->id)->result_array();
				if($items[0]['ids']){
					$locked_items = ' AND item_id not in ('.$items[0]['ids'].')';
				}else{
					$locked_items = "";
				}

				$rewards		= LuckReward::find('1=1 AND type=1 '.$locked_items.'' . ($is_weekly ? ' AND weekly=1' : ''), array('reorder' => 'RAND()'));	

				$log			= new PlayerLuckLog();
				$choosen_reward	= false;

				if($_POST['currency'] == 1) {
					$player->spend($needed_currency);
					$log->currency	= $needed_currency;
				} else {
					$user->spend($needed_credits);
					$log->credits	= $needed_credits;
				}

				while(true) {
					foreach($rewards as $reward) {
						if(rand(1, 100) <= $reward->chance) {
							$choosen_reward	= $reward;
							
							break 2;
						}
					}
				}

				if($is_weekly) {
					$week_data				= $this->default_week;
				} else {
					$week_data[date('N')]	= true;
				}

				$stats->luck_week_data	= serialize($week_data);
				$stats->save();

				$this->json->success	= true;
				$this->json->slot		= array($choosen_reward->slot1, $choosen_reward->slot2, $choosen_reward->slot3, $choosen_reward->slot4);
				$this->json->today		= date('N');

				$message	= '';
				
				if($choosen_reward->enchant_points){
					$message	.= highamount($choosen_reward->quantity) . ' ' . t('luck.index.names.8');
					$player->enchant_points_total += $choosen_reward->quantity;
				}
				
				if($choosen_reward->currency) {
					$message	.= highamount($choosen_reward->currency) . ' ' . t('currencies.' . $player->character()->anime_id);

					$player->earn($choosen_reward->currency);
				}
				if($choosen_reward->exp) {
					$message	.= highamount($choosen_reward->exp) . ' ' . t('attributes.attributes.exp2');

					$player->earn_exp($choosen_reward->exp);
				}

				if($choosen_reward->credits) {
					$message	.= highamount($choosen_reward->credits) . ' ' . t('currencies.credits');
					$user->earn($choosen_reward->credits);
					
					// Verifica os créditos do jogador.
					$player->achievement_check("credits");
					// Objetivo de Round
					$player->check_objectives("credits");
				}
				
				if($choosen_reward->equipment) {
					$message	.= highamount($choosen_reward->equipment) . ' ' . t('luck.index.header.equipment');
					Item::generate_equipment($player);
				}

				if($choosen_reward->item_id) {
					$item		= Item::find_first($choosen_reward->item_id);
					$message	.= $item->description()->name . ' x' . highamount($choosen_reward->quantity);

					$player->add_consumable($item, $choosen_reward->quantity);
				}

				$ats	= array(
					'for_atk'		=> t('formula.for_atk'),
					'for_def'		=> t('formula.for_def'),
					'for_crit'		=> t('formula.for_crit'),
					'for_abs'		=> t('formula.for_abs'),
					'for_prec'		=> t('formula.for_prec'),
					'for_init'		=> t('formula.for_init'),
					'for_inc_crit'	=> t('formula.for_inc_crit'),
					'for_inc_abs'	=> t('formula.for_inc_abs')
				);

				foreach ($ats as $key => $value) {
					if($choosen_reward->{$key}) {
						$attributes->{$key}	+= $choosen_reward->{$key};

						$message	.= t('luck.index.messages.point', array('count' => highamount($choosen_reward->$key), 'attribute' => $value));
					}
				}

				$log->player_id			= $player->id;
				$log->luck_reward_id	= $choosen_reward->id;
				$log->type				= 1;
				$log->save();

				if(!$is_weekly) {
					$player->luck_used	= 1;
				}

				$player->save();
				$attributes->save();
				
				// Verifica as conquistas do Sorte - Conquista
				$player->achievement_check("luck");
				// Objetivo de Round
				$player->check_objectives("luck");

				$this->json->message	= t('luck.index.won', array('prize' => $message));
				$this->json->currency	= $player->currency;
				$this->json->currency	= $player->exp;
				$this->json->credits	= $user->credits;
			} else {
				$this->json->errors	= $errors;
			}
		}
		function summoning() {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();
			$player					= Player::get_instance();
			$user					= User::get_instance();			
						
			if(isset($_POST['currency']) && is_numeric($_POST['currency'])) {
				$needed_currency	= $this->summon_currency;
				$needed_credits		= $this->summon_credits;

				if($_POST['currency'] == 1 && $player->currency < $needed_currency) {
					$errors[]	= t('luck.errors.currency', array('currency' => t('currencies.' . $player->character()->anime_id)));
				}

				if($_POST['currency'] != 1 && $user->credits < $needed_credits) {
					$errors[]	= t('luck.errors.currency', array('currency' => t('currencies.credits')));
				}
			}else{
				$errors[]	= "Inválido";
			}

			if(!sizeof($errors)) {
				$items			= Recordset::query('select group_concat(item_id) as ids from player_items WHERE item_id in (select item_id from luck_rewards WHERE item_type_id=6) AND player_id='. $player->id)->result_array();
				if($items[0]['ids']){
					$locked_items = ' AND item_id not in ('.$items[0]['ids'].')';
				}else{
					$locked_items = "";
				}
				switch($_POST['type_reward']){
					case "character":
						$type = "AND (character_id > 0 || chance = 50)";
					break;
					case "character_theme":
						$type = "AND (character_theme_id > 0 || chance = 50)";
					break;
					case "pets":
						$type = "";
					break;
				}

				$rewards		= LuckReward::find('1=1 AND type=2 '. $type . ' ' . $locked_items, array('reorder' => 'RAND()'));	
				$log			= new PlayerLuckLog();
				$choosen_reward	= false;

				if($_POST['currency'] == 1) {
					$player->spend($needed_currency);
					$log->currency	= $needed_currency;
				} else {
					$user->spend($needed_credits);
					$log->credits	= $needed_credits;
				}

				while(true) {
					foreach($rewards as $reward) {
						if($_POST['currency'] == 1){
							if(rand(1, 100) <= $reward->chance) {
								$choosen_reward	= $reward;
								
								break 2;
							}
						}else{
							if(rand(1, 100) <= $reward->chance_credits) {
								$choosen_reward	= $reward;
								
								break 2;
							}
						}
					}
				}

				$this->json->success	= true;
				$this->json->slot		= array($choosen_reward->slot1, $choosen_reward->slot2, $choosen_reward->slot3, $choosen_reward->slot4);
				$this->json->today		= date('N');

				$message	= '';

				// Nada
				if (!$choosen_reward->character_id && !$choosen_reward->character_theme_id) {
					$message	.= t('tutorial.nada');
				}

				//Prêmios ( CHARACTERS )
				if ($choosen_reward->character_id) {
					$reward_character				= new UserCharacter();
					$reward_character->user_id		= $player->user_id;
					$reward_character->character_id	= $choosen_reward->character_id;
					$reward_character->was_reward	= 1;
					$reward_character->save();
					
					$message	.= Character::find($choosen_reward->character_id)->description()->name;
					
					// verifica se desbloqueou novo personagem - conquista
					$player->achievement_check("character");
					// Objetivo de Round
					$player->check_objectives("character");
					
					if($reward->chance==2){
						global_message('hightlights.circulo', TRUE,[
							$player->name,
							Character::find($choosen_reward->character_id)->description()->name
						]);
					}
				}
				//Prêmios ( THEME )
				if ($choosen_reward->character_theme_id) {
					$reward_theme						= new UserCharacterTheme();
					$reward_theme->user_id				= $player->user_id;
					$reward_theme->character_theme_id	= $choosen_reward->character_theme_id;
					$reward_theme->was_reward			= 1;
					$reward_theme->save();
					
					$message	.= CharacterTheme::find($choosen_reward->character_theme_id)->description()->name;
					
					// verifica se desbloqueou novo personagem - conquista
					$player->achievement_check("character_theme");
					// Objetivo de Round
					$player->check_objectives("character_theme");

				}
				
				$log->player_id			= $player->id;
				$log->luck_reward_id	= $choosen_reward->id;
				$log->type				= 2;
				$log->save();
				

				$this->json->message	= t('luck.index.won2', array('prize' => $message));
				$this->json->currency	= $player->currency;
				$this->json->currency	= $player->exp;
				$this->json->credits	= $user->credits;
			} else {
				$this->json->errors	= $errors;
			}
		}
	}