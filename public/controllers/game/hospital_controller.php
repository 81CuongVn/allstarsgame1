<?php
	class HospitalController extends Controller {
		private	$cost	= 0;

		function __construct() {
			parent::__construct();

			$player		= Player::get_instance();
			$this->cost	= 20 * $player->level;
		}

		function index() {
			$player		= Player::get_instance();
			$this->assign('currency', t('currencies.' . $player->character()->anime_id));
			$this->assign('cost', $this->cost);
			$this->assign('player', $player);
		}

		function heal() {
			$this->as_json			= true;
			$this->json->success	= true;
			$errors					= [];
			$player					= Player::get_instance();

			if($player->currency < $this->cost) {
				$errors[]	= t('hospital.errors.currency', [
					'currency' => t('currencies.' . $player->character()->anime_id)
				]);
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;

				$player->spend($this->cost);
				$player->less_life	= 0;
				$player->less_mana	= 0;
				$player->hospital	= 0;
				$player->save();
			} else {
				$this->json->messages	= $errors;
			}
		}
	}