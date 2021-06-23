<?php
class TrainingsController extends Controller {
	public function attributes() {
		$player	= Player::get_instance();

		$this->assign('player',				$player);
		$this->assign('player_tutorial',	$player->player_tutorial());
	}

	public function train_attribute() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$errors					= [];

		if (isset($_POST['stamina']) && is_numeric($_POST['stamina']) && $_POST['stamina'] > 0) {
			$points		= 30 * $_POST['stamina'];
			$points		+= percent($player->attributes()->sum_bonus_training_earn, $points);
			$points		= floor($points);

			if ($_POST['stamina'] > $player->for_stamina()) {
				$errors[]	= t('attributes.attributes.errors.stamina');
			}

			if ($player->weekly_points_spent >= $player->max_attribute_training()) {
				$errors[]	= t('attributes.attributes.errors.limit');
			}
		} else {
			$errors[]	= t('attributes.attributes.errors.quantity');
		}

		if (!sizeof($errors)) {
			$exp_multiplier	= floor(30 - ($player->level / 10));
			$exp			= $exp_multiplier > 0 ? $exp_multiplier * $_POST['stamina'] : 0;
			$exp			-= percent($player->attributes()->sum_bonus_training_exp, $points);
			$exp			= floor($exp);

			$player->less_stamina			+= $_POST['stamina'];
			$player->exp					+= $exp;

			if ($player->weekly_points_spent + $points > $player->max_attribute_training()) {
				$real_points					= $player->max_attribute_training() - $player->weekly_points_spent;
				$player->training_total			+= $real_points;
				$player->weekly_points_spent	=  $player->max_attribute_training();
			} else {
				$real_points					= $points;
				$player->training_total			+= $points;
				$player->weekly_points_spent	+= $points;
			}

			$player->save();

			$this->json->success			= true;
			$this->json->exp				= $exp;
			$this->json->points				= $real_points;

			$this->json->level				= $player->level;
			$this->json->exp_player			= $player->exp;
			$this->json->level_exp			= $player->level_exp();

			if($player->exp > $player->level_exp()) {
				$this->json->level_redirect	= 1;
			}

			$this->json->stamina			= $player->for_stamina();
			$this->json->max_stamina		= $player->for_stamina(true);
			$this->json->view				= partial('traning_limit', [
				'player'		=> $player,
				'spent_stamina'	=> $_POST['stamina'],
				'earn_points'	=> $real_points,
				'earn_exp'		=> $exp
			]);
		} else {
			$this->json->errors	= $errors;
		}
	}

	public function distribute_attribute() {
		global $attrRate;

		$this->as_json		= true;

		$player				= Player::get_instance();
		$max				= 0;
		$avail				= $player->available_training_points();
		$allowed_attributes	= ['for_atk', 'for_def', 'for_crit', 'for_abs', 'for_prec', 'for_init','for_inc_crit','for_inc_abs'];
		$errors				= [];

		// Normal point distribution
		if (isset($_POST['attribute']) && in_array($_POST['attribute'], $allowed_attributes) && isset($_POST['quantity']) && is_numeric($_POST['quantity'])) {
			if ($_POST['quantity'] < 1) {
				$errors[]	= t('attributes.distribute.errors.invalid');
			}

			if ($_POST['quantity'] > $avail) {
				$errors[]	= t('attributes.distribute.errors.enough');
			}

			if (!sizeof($errors)) {
				$player->{$_POST['attribute']}	+= $_POST['quantity'];
				$player->training_points_spent	+= $_POST['quantity'];
				$player->save();

			}
		}

		// General point distribution
		if (isset($_POST['general']) && isset($_POST['data']) && is_array($_POST['data'])) {
			$total	= 0;
			$update	= [];

			foreach ($_POST['data'] as $data) {
				if (isset($data['attribute']) && isset($data['quantity']) && in_array($data['attribute'], $allowed_attributes) && is_numeric($data['quantity'])) {
					$update[]	= $data;
					$total		+= $data['quantity'];
				}
			}

			if ($total <= $avail) {
				foreach ($update as $attribute) {
					$player->{$attribute['attribute']}	+= ($attribute['quantity'] < 0 ? 0 : $attribute['quantity']) ;
					$player->training_points_spent		+= ($attribute['quantity'] < 0 ? 0 : $attribute['quantity']);
				}

				$player->save();
			}
		}

		$attributes	= [
			'for_atk'		=> t('formula.tooltip.title.for_atk'),
			'for_def'		=> t('formula.tooltip.title.for_def'),
			'for_crit'		=> t('formula.tooltip.title.for_crit'),
			'for_abs'		=> t('formula.tooltip.title.for_abs'),
			'for_prec'		=> t('formula.tooltip.title.for_prec'),
			'for_init'		=> t('formula.tooltip.title.for_init'),
			'for_inc_crit'	=> t('formula.tooltip.title.for_inc_crit'),
			'for_inc_abs'	=> t('formula.tooltip.title.for_inc_abs')
		];

		foreach ($attributes as $_ => $attribute) {
			$value	= $player->{$_};
			if ($value > $max) {
				$max	= $value;
			}
		}

		$this->json->mana			= $player->for_mana();
		$this->json->max_mana		= $player->for_mana(true);
		$this->json->stamina		= $player->for_stamina();
		$this->json->max_stamina	= $player->for_stamina(true);
		$this->json->view			= partial('distribute_attribute', [
			'max'			=> $max,
			'player'		=> $player,
			'current_exp'	=> $player->training_to_next_point(true),
			'point_exp'		=> $player->training_to_next_point(),
			'points'		=> $player->available_training_points(),
			'attributes'	=> $attributes,
			'errors'		=> $errors,
			'attrRate'		=> $attrRate
		]);

		// verifica o level da conta do jogador
		$player->achievement_check("level_account");
		$player->check_objectives("level_account");
	}

	public function techniques() {
		$player				= Player::get_instance();
		$max_training		= $player->max_technique_training();
		$can_train			= $player->technique_training_spent < $max_training;
		$learned_techniques	= $player->learned_techniques();

		if($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= array();

			if($player->is_pvp_queued) {
				$errors[]	= t('techniques.training.errors.pvp_queue');
			}

			if((!isset($_POST['item']) || (isset($_POST['item']) && !is_numeric($_POST['item']))) || (!isset($_POST['duration']) || (isset($_POST['duration']) && !is_numeric($_POST['duration'])))) {
				$errors[]	= t('techniques.training.errors.invalid_data');
			} else {
				if(!between($_POST['duration'], 1, 3)) {
					$errors[]	= t('techniques.training.errors.invalid_duration');
				} else {
					$found	= false;

					foreach($learned_techniques as $technique) {
						if($technique->id == $_POST['item']) {
							$found = $technique;
							break;
						}
					}

					if(!$found) {
						$errors[]	= t('techniques.training.errors.invalid_technique');
					} else {
						if($found->level >= 5) {
							$errors[]	= t('techniques.training.max_level_reached');
						}
					}
				}
			}

			if(!sizeof($errors)) {
				$player->technique_training_id			= $_POST['item'];
				$player->technique_training_complete_at	= date('Y-m-d H:i:s', strtotime('+' . (30 * $_POST['duration']) . ' minute'));
				$player->technique_training_duration	= $_POST['duration'];
				$player->save();

				$this->json->success	= true;
			} else {
				$this->json->errors	= $errors;
			}
		} else {
			$this->assign('player', $player);
			$this->assign('techniques', $learned_techniques);
			$this->assign('max_training', $max_training);
			$this->assign('can_train', $can_train);
		}
	}

	public function technique_wait() {
		$player			= Player::get_instance();
		$diff			= get_time_difference(now(), $player->technique_training_complete_at);
		$technique		= PlayerItem::find($player->technique_training_id);
		$item			= $technique->item();
		$finished		= now() > strtotime($player->technique_training_complete_at);
		$max_training	= $player->max_technique_training();
		$traning_left	= $max_training - $player->technique_training_spent;

		if ($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;

			$errors					= [];

			if(isset($_POST['finish']) && $_POST['finish']) {
				if(!$finished) {
					$errors[]	= t('techniques.training.wait.errors.non_finished');
				}
			}

			if(!sizeof($errors)) {
				if(isset($_POST['finish']) && $_POST['finish']) {
					$exp								= $player->technique_training_duration * 1000;
					$exp								= $exp > $traning_left ? $traning_left : $exp;
					$stat								= $technique->stats();
					$stat->exp							+= $exp;
					$player->technique_training_spent	+= $exp;

					// Level up?
					if($stat->exp >= $item->exp_needed_for_level()) {
						$stat->exp			-= $item->exp_needed_for_level();

						$technique->level	+= 1;
						$technique->save();
					}

					$stat->save();
				}

				$player->technique_training_id			= 0;
				$player->technique_training_complete_at	= NULL;
				$player->technique_training_duration	= 0;
				$player->save();

				$this->json->success	= true;
			} else {
				$this->json->errors	= $errors;
			}
		} else {
			$this->assign('player',		$player);
			$this->assign('diff',		$diff);
			$this->assign('technique',	$technique);
			$this->assign('finished',	$finished);
		}
	}
}
