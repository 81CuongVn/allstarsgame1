<?php
class GraduationsController extends Controller {
	function index() {
		$player	= Player::get_instance();

		$this->assign('player',			$player);
		$this->assign('graduations',	Graduation::all());
	}

	function graduate($id) {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$player					= Player::get_instance();
		$errors					= [];

		if (is_numeric($id)) {
			$graduation	= Graduation::find($id);

			if (!$graduation) {
				$errors[]	= t('graduations.errors.invalid');
			} else {
				extract($graduation->has_requirement($player));

				if (!$has_requirement) {
					$errors[]	= t('graduations.errors.requirements');
				}

				if ($graduation->sorting <= $player->graduation()->sorting) {
					$errors[]	= t('graduations.errors.requirements');
				}
			}
		} else {
			$errors[]	= t('graduations.errors.invalid');
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			// TODO: Disparar mensagem para o usuário

			$player->graduation_id	= $graduation->id;
			$player->save();

			// Level da Conta ( Graduação )
			$user = User::get_instance();
			$user->exp	+= $graduation->sorting * 50;
			$user->save();
		} else {
			$this->json->errors		= $errors;
		}
	}
}
