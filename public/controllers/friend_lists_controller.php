<?php
class FriendListsController extends Controller {
	function index() {
		$player	= Player::get_instance();
		$user	= User::get_instance();
		$friends = PlayerFriendList::find("player_id=". $player->id);

		// Verifica se tem direito a conquista de amigo - Conquista
		$player->achievement_check("friends");

		if ($friends) {
			foreach ($friends as $friend) {
				$players[] = Player::find_first($friend->friend_id);
			}
			$this->assign('friends', $players);
			$this->assign('quest_counters', $player->quest_counters());
		} else {
			$this->assign('friends', false);
		}
		$this->assign('player', $player);
		$this->assign('user', $user);

	}
	function list_status() {
		$this->layout	= false;

		if(is_numeric($_GET['player_id'])) {
			$player		= Player::find_first($_GET['player_id']);

			if(!$player) {
				$errors[]	= t('friends.errors.player_not_found');
			}

		} else {
			$errors[]	= t('friends.errors.invalid');
		}
		$formulas	= array(
			'for_atk'		=> t('formula.for_atk'),
			'for_def'		=> t('formula.for_def'),
			'for_crit'		=> t('formula.for_crit'),
			'for_crit_inc'	=> t('formula.for_inc_crit'),
			'for_abs'		=> t('formula.for_abs'),
			'for_abs_inc'	=> t('formula.for_inc_abs'),
			'for_prec'		=> t('formula.for_prec'),
			'for_init'		=> t('formula.for_init'),
		);

		$this->assign('player', $player);

		$this->assign('formulas', $formulas);
		$this->assign('quest_counters', $player->quest_counters());

		$max	= 0;
		$max2	= 0;

		foreach ($formulas as $_ => $formula) {
			$value	= $player->{$_}();

			if($value > $max) {
				$max	= $value;
			}
		}

		$this->assign('max', $max);
	}
	function search(){
		$player	= Player::get_instance();
		$this->assign('player',$player);

		// Verifica se tem direito a conquista de amigo - Conquista
		$player->achievement_check("friends");
	}
	function gift(){
		$player					= Player::get_instance();
		$user					= User::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if ((!isset($_POST['gift']) || (isset($_POST['gift']) && !is_numeric($_POST['player']))) && (!isset($_POST['player']) || (isset($_POST['player']) && !is_numeric($_POST['player'])))) {
			$errors[]	= t('friends.errors.invalid');
		} else {
			$gift			= $_POST['gift'];
			$player_gift	= Player::find_first($_POST['player']);
			$user_gift		= User::find_first($player_gift->user_id);

			if (sizeof($player->limit_by_day($player->id)) >= 1) {
				$errors[]	= t('friends.f26');
			}
			if ($gift == 1 && $player->currency < 2000) {
				$errors[]	= t('friends.errors.currency');
			}
			if ($gift == 2 && $user->credits < 1) {
				$errors[]	= t('friends.errors.credits');
			}
			if ($gift == 3 && $user->credits < 2) {
				$errors[]	= t('friends.errors.credits');
			}
			if ($gift == 4 && $user->credits < 2) {
				$errors[]	= t('friends.errors.credits');
			}
			if ($gift == 5 && $user->credits < 3) {
				$errors[]	= t('friends.errors.credits');
			}
			if ($gift == 1 && $user->level < 5) {
				$errors[]	= t('friends.errors.user_level');
			}
			if ($gift == 2 && $user->level < 10) {
				$errors[]	= t('friends.errors.user_level');
			}
			if ($gift == 3 && $user->level < 20) {
				$errors[]	= t('friends.errors.user_level');
			}
			if ($gift == 4 && $user->level < 30) {
				$errors[]	= t('friends.errors.user_level');
			}
			if ($gift == 5 && $user->level < 40) {
				$errors[]	= t('friends.errors.user_level');
			}

			if (!sizeof($errors)) {
				if ($gift == 1) {
					// Dá o Dinheiro para o Amigo.
					$player_gift->earn(2000);

					// Tira o seu dinheiro.
					$player->spend(2000);

					// Envia uma mensagem para o jogador avisando do prêmio
					$pm				= new PrivateMessage();
					$pm->to_id		= $player_gift->id;
					$pm->subject	= t('friends.f15');
					$pm->content	= t('friends.f16', ['name' => $player->name]);
					$pm->save();
				}

				if ($gift == 2) {
					// Dá o Equipamento para o Amigo.
					Item::generate_equipment($player_gift);

					// Tira o seu crédito.
					$user->spend(1);

					// Envia uma mensagem para o jogador avisando do prêmio
					$pm				= new PrivateMessage();
					$pm->to_id		= $player_gift->id;
					$pm->subject	= t('friends.f15');
					$pm->content	= t('friends.f17', ['name' => $player->name]);
					$pm->save();
				}

				if ($gift == 3) {
					// Dá 2 Estrelas para o Amigo.
					$user_gift->earn(2);

					// Tira o seu crédito.
					$user->spend(2);

					// Verifica os créditos do jogador.
					$player_gift->achievement_check("credits");

					// Envia uma mensagem para o jogador avisando do prêmio
					$pm				= new PrivateMessage();
					$pm->to_id		= $player_gift->id;
					$pm->subject	= t('friends.f15');
					$pm->content	= t('friends.f18', ['name' => $player->name]);
					$pm->save();
				}

				if ($gift == 4) {
					// Dá um Tema Random
					$theme		= CharacterTheme::find_first('price_credits > 0 or price_currency > 0', ['reorder' => 'RAND()']);
					$user_theme	= UserCharacterTheme::find_first("user_id = {$player_gift->user_id} and character_theme_id = ". $theme->id);
					if (!$user_theme) {
						$reward_theme						= new UserCharacterTheme();
						$reward_theme->user_id				= $player_gift->user_id;
						$reward_theme->character_theme_id	= $theme->id;
						$reward_theme->was_reward			= 0;
						$reward_theme->save();

						// Tira o seu crédito.
						$user->spend(2);

						// Envia uma mensagem para o jogador avisando do prêmio
						$pm				= new PrivateMessage();
						$pm->to_id		= $player_gift->id;
						$pm->subject	= t('friends.f15');
						$pm->content	= t('friends.f19', ['name' => $player->name, 'tema'=> $theme->description()->name]);
						$pm->save();
					} else {
						$errors[]	= t('friends.errors.tema');
					}

				}

				if ($gift == 5) {
					// Dá um mascore random
					$npc_pet	= Item::find_first('item_type_id=3 AND is_initial=1', ['reorder' => 'RAND()']);
					if (!$player_gift->has_item($npc_pet->id)) {
						$player_pet				= new PlayerItem();
						$player_pet->item_id	= $npc_pet->id;
						$player_pet->player_id	= $player_gift->id;
						$player_pet->save();

						// Tira o seu crédito.
						$user->spend(3);

						// Envia uma mensagem para o jogador avisando do prêmio
						$pm				= new PrivateMessage();
						$pm->to_id		= $player_gift->id;
						$pm->subject	= t('friends.f15');
						$pm->content	= t('friends.f20', [
							'name'		=> $player->name,
							'mascote'	=> $npc_pet->description()->name
						]);
						$pm->save();
					} else {
						$errors[]	= t('friends.errors.pet');
					}
				}

				// Insire na Tabela de Log.
				$player_gift_log			= new PlayerGiftLog();
				$player_gift_log->player_id	= $player->id;
				$player_gift_log->friend_id	= $player_gift->id;
				$player_gift_log->gift_id = $gift;
				$player_gift_log->save();

				$this->json->success	= true;
			} else {
				$this->json->messages	= $errors;
			}
		}
	}
	function make_list() {
		$this->layout			= false;
		$player					= Player::get_instance();
		$players 				= "";
		$my_friends				= PlayerFriendList::find("player_id=". $player->id);

		if(sizeof($my_friends)){
			foreach($my_friends as $my_friend){
				$ids[] = $my_friend->friend_id;
			}
			$my_friends = 'AND id not in ('.implode(",",$ids).')';
		}else{
			$my_friends = '';
		}

		if (isset($_POST['nome']) && $_POST['nome']) {
			$filter	= 'name LIKE "%' . addslashes($_POST['nome']) . '%" AND user_id not in('.$player->user_id.') AND removed=0 AND level > 9 '.$my_friends.'';
			$players = Player::find($filter);
		}

		$this->assign('players', 	$players);
		$this->assign('player', 	$player);
		$this->assign('requests',   PlayerFriendRequest::find("friend_id=". $player->id));

	}
	function kick() {
		$player					= Player::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$friend_player = PlayerFriendList::find_first("friend_id=".$_POST['id']." AND player_id=".$player->id);
			$player_friend = PlayerFriendList::find_first("player_id=".$_POST['id']." AND friend_id=".$player->id);

			if(!$player_friend->id && !$friend_player->id){
				$errors[]	= t('friends.errors.invalid');
			}

		} else {
			$errors[]	= t('friends.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$pm				= new PrivateMessage();
			$pm->to_id		= $_POST['id'];
			$pm->subject	= t('friends.kick_title');
			$pm->content	= t('friends.kick_description', ['name' => $player->name]) . "<hr />" . htmlspecialchars($_POST['reason']);
			$pm->save();

			$player_friend->destroy();
			$friend_player->destroy();

		} else {
			$this->json->messages	= $errors;
		}
	}

	function send($id = null){
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];
		$player					= Player::get_instance();

		if (is_numeric($id)) {
			$friend	= Player::find($id);

			if($friend->removed==1){
				$errors[]	= t('friends.errors.removed');
			}
		} else {
			$errors[]	= t('friends.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$request					= new PlayerFriendRequest();
			$request->player_id			= $player->id;
			$request->friend_id			= $friend->id;
			$request->save();
		} else {
			$this->json->messages	= $errors;
		}
	}
	function enter_accept($id = NULL) {
		$this->_enter_or_refuse($id);
	}

	function enter_refuse($id = NULL) {
		$this->_enter_or_refuse($id, true);
	}
	function remove_all(){
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		$player_friend_requests = PlayerFriendRequest::find("friend_id=".$player->id);

		if(!$player_friend_requests){
			$errors[]	= t('organizations.remove_error');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			//Deleta os pedidos de amizade
			foreach($player_friend_requests as $player_friend_request){
				$player_friend_request->destroy();
				$player_friend_request->save();
			}

		} else {
			$this->json->messages	= $errors;
		}
	}
	private function _enter_or_refuse($id = NULL, $is_refuse = false) {
		$this->as_json			= true;
		$this->json->success	= false;
		$player					= Player::get_instance();
		$errors					= [];

		if (isset($id) && is_numeric($id)) {
			$player_friend = PlayerFriendRequest::find_first("player_id=".$id." AND friend_id=".$player->id);
			$friend_player = PlayerFriendRequest::find_first("friend_id=".$id." AND player_id=".$player->id);

			if(!$player_friend->id){
				$errors[]	= t('friends.errors.send');
			}

		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$pm	= new PrivateMessage();
			$pm->to_id	= $id;


			if ($is_refuse) {
				$pm->subject	= t('friends.accept.no_friend');
				$pm->content	= t('friends.accept.refuse_message', ['name' => $player->name]) . "<hr />";
			} else {
				$pm->subject	= t('friends.accept.new_friend');
				$pm->content	= t('friends.accept.accept_message', ['name' => $player->name]);

				$accept_player_to_friend					= new PlayerFriendList();
				$accept_player_to_friend->player_id			= $player->id;
				$accept_player_to_friend->friend_id			= $player_friend->player_id;
				$accept_player_to_friend->save();

				$accept_friend_to_player					= new PlayerFriendList();
				$accept_friend_to_player->friend_id			= $player->id;
				$accept_friend_to_player->player_id			= $player_friend->player_id;
				$accept_friend_to_player->save();

			}

			$pm->save();
			$player_friend->destroy();

			if($friend_player){
				$friend_player->destroy();
			}
		} else {
			$this->json->messages	= $errors;
		}
	}
}
