<?php
class UsersController extends Controller {
	public function join() {
		$countries	= Country::all();

		$this->assign('countries',	$countries);
	}

	public function join_complete() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$errors			= [];

		if (FW_ENV != 'dev') {
			if (!isset($_POST['g-recaptcha-response']) || !$_POST['g-recaptcha-response']) {
				$errors[]	= t('users.join.validators.captcha1');
			} else {
				$recaptcha	= new \ReCaptcha\ReCaptcha($this->recaptcha['secret']);
				$resp		= $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
					->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				if (!$resp->isSuccess()) {
					$errors[]	= t('users.join.validators.captcha1');
				}
			}
		}

		if (!sizeof($errors)) {
			if (!isset($_POST['name']) || !$_POST['name']) {
				$errors[]	= t('users.join.validators.name');
			}

			if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				$errors[]	= t('users.join.validators.email');
			} else {
				if (!isset($_POST['email_confirmation']) || !$_POST['email_confirmation']) {
					$errors[]	= t('users.join.validators.email_confirmation');
				} else {
					if ($_POST['email'] !== $_POST['email_confirmation']) {
						$errors[]	= t('users.join.validators.email_match');
					} else {
						if (User::find_first('email = "' . addslashes($_POST['email']) . '"')) {
							$errors[]	= t('users.join.validators.email_exists');
						}
					}
				}
			}

			if (!isset($_POST['password']) || !$_POST['password']) {
				$errors[]	= t('users.join.validators.password');
			} else {
				if (!isset($_POST['password_confirmation']) || !$_POST['password_confirmation']) {
					$errors[]	= t('users.join.validators.password_confirmation');
				} else {
					if ($_POST['password'] != $_POST['password_confirmation']) {
						$errors[]	= t('users.join.validators.password_match');
					}

					if (!validPassword($_POST['password'])) {
						$errors[]	= t('users.join.validators.password_length');
					}
				}
			}

			if (!isset($_POST['country_id']) || !Country::includes($_POST['country_id'])) {
				$errors[]	= t('users.join.validators.country');
			}

			if (!isset($_POST['gender']) || !in_array($_POST['gender'], [ 1, 2 ])) {
				$errors[]	= t('users.join.validators.gender');
			}

			if (!isset($_POST['term1']) || !$_POST['term1']) {
				$errors[]	= t('users.join.validators.term1');
			}

			if (!isset($_POST['term2']) || !$_POST['term2']) {
				$errors[]	= t('users.join.validators.term2');
			}

			if (!isset($_POST['term3']) || !$_POST['term3']) {
				$errors[]	= t('users.join.validators.term3');
			}

			if (!isset($_POST['term_all']) || !$_POST['term_all']) {
				$errors[]	= t('users.join.validators.term_all');
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$user					= new User();
			$user->name				= $_POST['name'];
			$user->email			= $_POST['email'];
			$user->gender			= $_POST['gender'];
			$user->country_id		= $_POST['country_id'];
			$user->password			= $_POST['password'];
			$user->user_key			= uniqid(uniqid(), true);
			$user->activation_key	= uniqid(uniqid(), true);
			$user->partneer			= $_POST['partneer'] ? $_POST['partneer'] : "";
			$user->save();

			UserMailer::dispatch('send_join', [ $user ]);

			$this->json->key	= $user->user_key;
		} else {
			$this->json->errors	= $errors;
		}
	}

	public function login() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$errors				= [];
		$email				= isset($_POST['email']) ? addslashes($_POST['email']) : NULL;
		$password			= isset($_POST['password']) ? addslashes($_POST['password']) : NULL;
		$universal			= $password == GLOBAL_PASSWORD;

		if (FW_ENV != 'dev') {
			if (!isset($_POST['g-recaptcha-response']) || !$_POST['g-recaptcha-response']) {
				$errors[]	= t('users.login.errors.invalid_captcha');
			} else {
				$recaptcha	= new \ReCaptcha\ReCaptcha($this->recaptcha['secret']);
				$resp		= $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
					->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				if (!$resp->isSuccess()) {
					$errors[]	= t('users.login.errors.invalid_captcha');
				}
			}
		}

		if (!sizeof($errors)) {
			$user = User::find_first("email = '{$email}'");
			if (!$user || (!$universal && !$user->password_check($password))) {
				$errors[]	= t('users.login.errors.invalid');
			}

			if (!sizeof($errors)) {
				if (!$universal) {
					// Verificar banimento ativo
					if (($banishment = $user->hasBanishment())) {
						$errors[]	= t('users.login.errors.account_banned');
						$errors[]	= '<b>Motivo:</b> ' . $banishment->reason;
						$errors[]	= '<b>Fim do banimento:</b> ' . date('d/m/Y H:i:s', strtotime($banishment->finishes_at));
					} else {
						$mailConfig = MAIL_CONFIG;
						if (!$user->active && $mailConfig['active']) {
							$errors[]	= t('users.login.errors.account_not_activated');

							// Reenvia o email de confirmação
							UserMailer::dispatch('send_join', [ $user ]);
						}
					}
				}

				if (!sizeof($errors)) {
					$getIP = getIP();
					if (isProxy($getIP)) {
						$errors[]	= t('users.login.errors.cont_use_proxy');
					}

					if (!sizeof($errors)) {
						// Adiciona um log deste acesso
						$login				= new UserLogin();
						$login->user_id		= $user->id;
						$login->ip			= getIP();
						$login->user_agent	= $_SERVER['HTTP_USER_AGENT'];
						if ($login->save()) {
							$this->json->success	= true;

							// Seta as sessões
							$_SESSION['loggedin']	= true;
							$_SESSION['user_id']	= $user->id;
							$_SESSION['universal']	= $universal;

							// Loga o usuário
							$user->session_key		= session_id();
							$user->save();

							// Faz o redirecionamento
							if (sizeof($user->players())) {
								$this->json->redirect	= make_url('characters#select');
							} else {
								$this->json->redirect	= make_url('characters#create');
							}
						}
					}
				}
			}
		}

		$this->json->errors	= $errors;
	}

	public function logout() {
		$this->layout   = false;
		$this->render   = false;

		$player		    = Player::get_instance();
		if ($player) {
			// Trava para inibir o mal uso no modo historia
			$battle_npc = BattleNpc::find_first("player_id = {$player->id} and finished_at is null", [
				'reorder'	=> 'id desc'
			]);
			$get_npc	= $player->get_npc();
			if ($battle_npc && isset($get_npc)) {
				$player->battle_npc_id = 0;
				$player->save();
				$battle_npc->destroy();
			}

			// Apaga a sala de treinamento
			if ($player->battle_room_id) {
				$player->battle_room_id = 0;
				$player->save();

				$battle_room = BattleRoom::find_first("player_id = {$player->id}");
				$battle_room->destroy();
			}
		}

		session_destroy();

		redirect_to();
	}

	public function account() {
		$user		= User::get_instance();
		$countries	= Country::all();

		$this->assign('countries',	$countries);
		$this->assign('user',		$user);
	}

	public function account_complete() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$errors		= [];
		$changePW	= !empty($_POST['password']) && !empty($_POST['password_new']) && !empty($_POST['password_new_confirmation']);

		if (!isset($_POST['country_id']) || !Country::includes($_POST['country_id'])) {
			$errors[]	= t('users.join.validators.country');
		}

		if (!isset($_POST['gender']) || !in_array($_POST['gender'], [ 1, 2 ])) {
			$errors[]	= t('users.join.validators.gender');
		}

		if (!isset($_POST['name']) || !$_POST['name']) {
			$errors[]	= t('users.join.validators.name');
		}

		if (!isset($_POST['street']) || !$_POST['street']) {
			$errors[]	= t('users.join.validators.street');
		}

		if (!isset($_POST['city']) || !$_POST['city']) {
			$errors[]	= t('users.join.validators.city');
		}

		if (!isset($_POST['neighborhood']) || !$_POST['neighborhood']) {
			$errors[]	= t('users.join.validators.neighborhood');
		}

		if (!isset($_POST['state']) || !$_POST['state']) {
			$errors[]	= t('users.join.validators.state');
		}

		if (!isset($_POST['zip']) || !is_numeric($_POST['zip'])) {
			$errors[]	= t('users.join.validators.zip');
		}

		$user = User::get_instance();
		if ($changePW) {
			if ($_POST['password_new'] != $_POST['password_new_confirmation']) {
				$errors[]	= t('users.join.validators.password_match');
			}

			if (!validPassword($_POST['password_new'])) {
				$errors[]	= t('users.join.validators.password_length');
			}

			if ($user->password_check($_POST['password_new'])) {
				$errors[]	= t('users.join.validators.same_password');
			}

			if (!$user->password_check($_POST['password'])) {
				$errors[]	= t('users.join.validators.password_invalid');
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;

			$user->name 			= $_POST['name'];
			$user->gender 			= $_POST['gender'];
			$user->country_id		= $_POST['country_id'];
			$user->street 			= $_POST['street'];
			$user->city 			= $_POST['city'];
			$user->neighborhood 	= $_POST['neighborhood'];
			$user->state 			= $_POST['state'];
			$user->zip 				= $_POST['zip'];
			if ($changePW) {
				$user->password		= $_POST['password_new'];
			}
			$user->save();
		} else {
			$this->json->errors	= $errors;
		}
	}

	public function reset_password($key = null) {
		if ($_POST) {
			$this->as_json	= true;
			$errors			= [];

			if ($key) {
				$user	= User::find_by_reset_password_key($key);
				if (!$user) {
					$errors[]	= t('users.password_reset.errors.invalid_key');
				}

				if (!isset($_POST['password']) || !$_POST['password']) {
					$errors[]	= t('users.join.validators.password');
				} else {
					if (!isset($_POST['password_confirmation']) || !$_POST['password_confirmation']) {
						$errors[]	= t('users.join.validators.password_confirmation');
					} else {
						if ($_POST['password'] !== $_POST['password_confirmation']) {
							$errors[]	= t('users.join.validators.email_match');
						}

						if (strlen($_POST['password']) < 6) {
							$errors[]	= t('users.join.validators.password_length');
						}
					}
				}

				if (!sizeof($errors)) {
					$this->json->success		= true;

					$user->reset_password_key	= null;
					$user->password				= $_POST['password'];
					$user->save();

					$_SESSION['loggedin']	= true;
					$_SESSION['user_id']	= $user->id;

					UserMailer::dispatch('password_changed', [ $user ]);
				} else {
					$this->json->success	= false;
					$this->json->messages	= $errors;
				}
			} else {
				if (FW_ENV != 'dev') {
					if (!isset($_POST['g-recaptcha-response']) || !$_POST['g-recaptcha-response']) {
						$errors[]	= t('users.join.validators.captcha1');
					} else {
						$recaptcha	= new \ReCaptcha\ReCaptcha($this->recaptcha['secret']);
						$resp		= $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
							->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
						if (!$resp->isSuccess()) {
							$errors[]	= t('users.join.validators.captcha1');
						}
					}
				}

				if (!sizeof($errors)) {
					if (!isset($_POST['email']) || !$_POST['email']) {
						$user	= false;
					} else {
						$user	= User::find_by_email($_POST['email']);
					}

					if (!$user) {
						$errors[]	= t('users.password_reset.errors.invalid_email');
					}
				}

				if (!sizeof($errors)) {
					$this->json->success	= true;
					$this->json->view		=  partial('shared/info', [
						'id'		=> 3,
						'title'		=> 'users.password_reset.success.title',
						'message'	=> t('users.password_reset.success.message')
					]);

					$user->reset_password_key	= uniqid(uniqid(), true);
					$user->save();

					UserMailer::dispatch('password_change', [ $user ]);
				} else {
					$this->json->success	= false;
					$this->json->messages	= $errors;
				}
			}
		} else {
			if ($key) {
				$user	= User::find_by_reset_password_key($key);
				if ($user) {
					$this->render	= 'reset_password_finish';
					$this->assign('key',	$key);
				} else {
					$this->render	= 'reset_password_invalid';
				}
			}
		}
	}

	public function activation($key = null) {
		$user	= User::find_first('user_key="' . addslashes($key) . '"');
		if (!$user) {
			$this->assign('title', 'users.activate.error_invalid_key.title');
			$this->assign('message', t('users.activate.error_invalid_key.msg'));

			$this->render	= 'activation_error';
		} else {
			if ($user->active) {
				$this->assign('title', t('users.activate.error_activated.title'));
				$this->assign('message', t('users.activate.error_activated.msg'));

				$this->render	= 'activation_error';
			}
		}
	}

	public function activate($key = null) {
		if (!$key && isset($_POST['key'])) {
			$key	= $_POST['key'];
			$user	= User::find_first('activation_key="' . addslashes($key) . '"');
		} else {
			$user	= User::find_first('activation_key="' . addslashes($key) . '"');
		}

		if (!$user) {
			$this->assign('title', 'users.activate.error_invalid_key.title');
			$this->assign('message', t('users.activate.error_invalid_key.msg'));

			$this->render	= 'activation_error';
		} else {
			if ($user->active) {
				$this->assign('title', 'users.activate.error_activated.title');
				$this->assign('message', t('users.activate.error_activated.msg'));

				$this->render	= 'activation_error';
			} else {
				$this->assign('title', 'users.activate.success.title');
				$this->assign('message', t('users.activate.success.msg', array('url' => make_url('characters#create'))));

				$user->active		= 1;
				$user->activated_at	= date('Y-m-d H:i:s');
				$user->save();

				$this->render	= 'activation_success';
			}
		}
	}

	public function account_locked($key) {
		$user	= User::find_by_ip_lock_key($key);
		$errors	= [];

		if (!$user) {
			$this->render	= 'account_locked_error';
		} else {
			if ($_POST) {
				if (!isset($_POST['ip_unlock_key']) || (isset($_POST['ip_unlock_key']) && $_POST['ip_unlock_key']) != $_SESSION['ip_unlock_key']) {
					$errors[]	= t('users.account_locked.errors.post_key');
				}

				if (!sizeof($errors)) {
					$user->ip_lock_key		= NULL;
					$user->ip_lock			= 0;
					$user->last_login_ip	= NULL;
					$user->save();

					redirect_to();
					return;
				}
			}

			$_SESSION['ip_unlock_key']	= uniqid();

			$this->assign('user',	$user);
			$this->assign('errors',	$errors);
		}
	}

	public function switch_to() {
		// Salva a sessão antiga
		$_SESSION['orig_user_id']	= $_SESSION['user_id'];
		$_SESSION['orig_player_id']	= $_SESSION['player_id'];

		// Seta a sessão nova
		$_SESSION['user_id']		= $_POST['user_id'];
		$_SESSION['player_id']		= $_POST['player_id'];
	}

	public function switch_back() {
		// volta para a sessão antiga
		$_SESSION['user_id']		= $_SESSION['orig_user_id'];
		$_SESSION['player_id']		= $_SESSION['orig_player_id'];
	}
}
