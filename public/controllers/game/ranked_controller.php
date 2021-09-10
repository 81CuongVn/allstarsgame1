<?php
class RankedController extends Controller {
	function index() {
		$player		= Player::get_instance();
		$rankeds	= Ranked::find('started = 1 order by league asc');
		$tiers		= RankedTier::all(['reorder' => 'sort desc']);
		$best_rank	= PlayerRanked::find_first('player_id = ' . $player->id, [
			'reorder'	=> 'points desc',
			'limit'		=> 1
		]);

		if (!$_POST) {
			$ranked	= Ranked::find_first('started = 1 order by league desc');
		} else {
			$ranked	= Ranked::find_first($_POST['leagues']);
		}

		if ($ranked) {
			$player_ranked	= $player->ranked($ranked->id);
			if ($player_ranked)  {
				$player_ranked->update();
			}
		} else {
			$player_ranked	= FALSE;
		}

		$ranked_total	= false;
		if ($best_rank) {
			$ranked_total	= Recordset::query("SELECT SUM(wins) AS total_wins, SUM(losses) AS total_losses, SUM(draws) AS total_draws FROM player_rankeds WHERE player_id = {$player->id}")->row();
		}

		// Verifica se você tem liga completa - Conquista
		$player->achievement_check("league");
		$player->check_objectives("league");

		$this->assign('tiers',				$tiers);
		$this->assign('player',				$player);
		$this->assign('ranked',				$ranked);
		$this->assign('rankeds',			$rankeds);
		$this->assign('best_rank',			$best_rank);
		$this->assign('ranked_total',		$ranked_total);
		$this->assign('player_ranked',		$player_ranked);
		$this->assign('player_tutorial',	$player->player_tutorial());
	}

	function shop() {
	}

	function reward() {
		$this->as_json			= true;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$user					= User::get_instance();
		$errors					= [];
		$content				= "";

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$ranked			= Ranked::find($_POST['id']);
			if ($ranked || !$ranked->finished) {
				$player_ranked	=  $player->ranked($ranked->id);
				if (!$player_ranked) {
					$errors[]	= t('ranked.errors.not_league');
				} else {
					if ($player_ranked->reward) {
						$errors[]	= t('ranked.errors.no_reward');
					}

					if ($player->id != $player_ranked->player_id) {
						$errors[]	= t('ranked.errors.no_player');
					}
				}
			} else {
				$errors[]	= t('ranked.errors.not_league');
			}

			if (!sizeof($errors)) {
				$player_ranked->reward = 1;
				$player_ranked->save();

				//
				$rewards	= $ranked->reward($player_ranked->ranked_tier_id);
				if ($rewards->currency) {
					$player->earn($rewards->currency);
					$content .= highamount($rewards->currency) . " " . t('currencies.' . $player->character()->anime_id) . "<br />";

					$player->achievement_check("currency");
					$player->check_objectives("currency");
				}

				if ($rewards->exp) {
					$player->earn_exp($rewards->exp);
					$content .= highamount($rewards->exp) . " " . t('ranked.exp') . "<br />" ;
				}

				if ($rewards->credits) {
					$user->earn($rewards->credits);
					$content .= highamount($rewards->credits) . " " . t('treasure.show.credits'). "<br />";

					// Verifica os créditos do jogador.
					$player->achievement_check("credits");
					$player->check_objectives("credits");
				}

				if ($rewards->exp_user) {
					$user->exp($rewards->exp_user);
					$content .= highamount($rewards->exp_user) . " " . t('ranked.exp_account')."<br />";
				}

				if ($rewards->headline_id && !$user->is_headline_bought($rewards->headline_id)) {
					$reward_headline				= new UserHeadline();
					$reward_headline->user_id		= $player->user_id;
					$reward_headline->headline_id	= $rewards->headline_id;
					$reward_headline->save();

					$content .= t('treasure.show.headline') . " " . Headline::find($rewards->headline_id)->description()->name . "<br />";
				}

				if ($rewards->character_id && !$user->is_character_bought($rewards->character_id)) {
					$reward_character				= new UserCharacter();
					$reward_character->user_id		= $player->user_id;
					$reward_character->character_id	= $rewards->character_id;
					$reward_character->was_reward	= 1;
					$reward_character->save();

					$content .= t('treasure.show.character') . " " . Character::find($rewards->character_id)->description()->name . "<br />";
				}

				if ($rewards->character_theme_id && !$user->is_theme_bought($rewards->character_theme_id)) {
					$reward_theme						= new UserCharacterTheme();
					$reward_theme->user_id				= $player->user_id;
					$reward_theme->character_theme_id	= $rewards->character_theme_id;
					$reward_theme->was_reward			= 1;
					$reward_theme->save();

					$content .= t('treasure.show.theme') . " " . CharacterTheme::find($rewards->character_theme_id)->description()->name . "<br />";
				}

				if ($rewards->item_id) {
					$item		= Item::find_first($rewards->item_id);
					$player->add_consumable($item, $rewards->quantity);

					$content .= highamount($rewards->quantity) . "x " . Item::find($rewards->item_id)->description()->name . "<br />";
				}

				$pm				= new PrivateMessage();
				$pm->to_id		= $player->id;
				$pm->subject	= t("ranked.reward_league") . " - " . $player_ranked->tier()->description()->name;
				$pm->content	= $content;
				$pm->save();

				$this->json->success = true;
			}
		} else {
			$errors[]	= t('ranked.errors.not_league');
		}

		$this->json->messages	= $errors;
	}
}
