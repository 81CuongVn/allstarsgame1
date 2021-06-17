<?php
class PrivateMessagesController extends Controller {
	function index() {
		$player	= Player::get_instance();

		$this->assign('player', $player);
	}

	function messages($page = 0) {
		$this->layout	= false;

		$player	= Player::get_instance();
		$limit	= 10;
		$result	= (new PrivateMessage)->filter('AND removed=0 AND to_id=' . $player->id, $page, $limit);

		$this->assign('messages', $result['messages']);
		$this->assign('pages', $result['pages']);
		$this->assign('player', $player);
		$this->assign('messages2', PrivateMessage::find("to_id=".$player->id." and removed=0"));

		$this->assign('page', $page);
	}

	function read($id = null) {
		$this->layout	= false;
		$player			= Player::get_instance();
		$is_error		= false;

		if (is_numeric($id)) {
			$message	= PrivateMessage::find_first('to_id=' . $player->id . ' AND id=' . $id);

			if(!$message) {
				$is_error	= true;
			} else {
				if (!$message->read_at) {
					$message->read_at	= now(true);
					$message->save();
				}
			}
		} else {
			$is_error	= true;
		}

		$this->assign('is_error', $is_error);
		$this->assign('message', $message);
	}

	function reply($id = null) {
		$this->layout	= false;
		$player			= Player::get_instance();
		$reply_text		= '';
		$is_error		= false;
		$orig_message	= false;

		if (is_numeric($id)) {
			$orig_message	= PrivateMessage::find_first('to_id=' . $player->id . ' AND id=' . $id);

			if(!$orig_message) {
				$is_error	= true;
			} else {
				$reply_text	= "\n\n------------\n\n" . $orig_message->from()->name . "\n\n" . $orig_message->content;
			}
		} else {
			$is_error	= true;
		}

		$this->assign('reply_text', $reply_text);
		$this->assign('is_error', $is_error);
		$this->assign('orig_message', $orig_message);
	}

	function find_player() {
		$this->as_json	= true;

		$players	= [];
		$results	= RankingPlayer::find('name LIKE "' . $_GET['keyword'] . '%"', ['limit' => 10]);

		foreach ($results as $result) {
			$players[]	= [
				'id'	=> $result->player_id,
				'name'	=> $result->name,
				'level'	=> $result->level,
				'image'	=> $result->character_theme()->first_image()->small_image()
			];
		}

		$this->json	= $players;
	}

	function send() {
		$player					= Player::get_instance();
		$this->as_json			= true;
		$this->json->success	= false;
		$errors					= [];

		if (is_numeric($_POST['to_id'])) {
			if (!trim($_POST['subject'])) {
				$errors[]	= t('private_messages.send.errors.subject');
			}

			if (!trim($_POST['content'])) {
				$errors[]	= t('private_messages.send.errors.content');
			}
		} else {
			$errors[]	= t('private_messages.send.errors.to');
		}

		if (!sizeof($errors)) {
			$message			= new PrivateMessage();
			$message->from_id	= $player->id;
			$message->to_id		= $_POST['to_id'];

			if (isset($_POST['reply_id'])) {
				$message->reply_id	= $_POST['reply_id'];
			}

			$message->subject	= htmlspecialchars($_POST['subject']);
			$message->content	= htmlspecialchars($_POST['content']);
			$message->save();

			$this->json->success	= true;
		} else {
			$this->json->messages	= $errors;
		}

	}

	function delete() {
		$player	= Player::get_instance();

		if(isset($_POST['all']) && $_POST['all']) {
			$pms	= PrivateMessage::find('to_id=' . $player->id);
		}

		if (isset($_POST['ids']) && is_array($_POST['ids'])) {
			$pms	= PrivateMessage::find('to_id=' . $player->id . ' AND id IN(' . implode(',', $_POST['ids']) . ')');
		}

		foreach ($pms as $pm) {
			$pm->destroy();
		}

		$this->as_json			= true;
		$this->json->success	= true;
	}
}
