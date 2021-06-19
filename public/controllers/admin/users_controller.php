<?php
class UsersController extends Controller {
	public function index() {
		$user	= User::get_instance();

		$filter	= 'active';
		if (isset($_GET['filter']) && in_array($_GET['filter'], [
			'all', 'active',
			'inactive', 'vip', 'online'
		])) {
			$filter = $_GET['filter'];
		}

		$addWhere	= false;
		if ($filter == 'active') {
			$addWhere	= ' AND active = 1';
		} elseif ($filter == 'inactive') {
			$addWhere	= ' AND active = 0';
		} elseif ($filter == 'vip') {
			$addWhere	= ' AND vip = 1';
		} elseif ($filter == 'online') {
			$addWhere	= ' AND last_activity >= ' . time() - (15 * 60);
		}

		$page	= 1;
		if (isset($_GET['page']) && is_numeric($_GET['page'])) {
			$page = $_GET['page'];
		}

		$items_per_page	= 12;
		$all_users		= Recordset::query("SELECT COUNT(id) AS total FROM users WHERE removed = 0" . $addWhere)->row()->total;
		$pages			= ceil($all_users / $items_per_page);
		$page			= (!is_numeric($page) || $page <= 0) ? 1 : $page;
		$page			= ($page > $pages) ? $pages : $page;
		$start			= ceil(($page * $items_per_page) - $items_per_page);
		$start			= $start < 0 ? 0 : $start;
		$users			= User::find('1=1' . $addWhere, [
			'limit'		=> $start . ', ' . $items_per_page,
			'reorder'	=> $filter != 'vip' ? 'id asc' : 'credits desc'
		]);

		// Países
		$countries	= Country::all();

		$this->assign('user',	$user);
		$this->assign('page',	$page);
		$this->assign('pages',	$pages);
		$this->assign('users',	$users);
		$this->assign('filter',	$filter);

		$this->assign('countries',	$countries);
	}

	public function view($id) {
		if (!$id) {
			redirect_to('admin/users');
			exit;
		}

		$u	= User::find_first('id = ' . $id);
		if (!$u) {
			redirect_to('admin/users');
			exit;
		}

		$page			= !isset($_GET['page']) || !is_numeric($_GET['page']) ? 1 : $_GET['page'];
		$items_per_page	= 12;
		$all_players	= Recordset::query("SELECT COUNT(id) AS total FROM players WHERE user_id = '{$u->id}' AND removed = 0")->row()->total;
		$pages			= ceil($all_players / $items_per_page);
		$page			= (!is_numeric($page) || $page <= 0) ? 1 : $page;
		$page			= ($page > $pages) ? $pages : $page;
		$start			= ceil(($page * $items_per_page) - $items_per_page);
		$start			= $start < 0 ? 0 : $start;
		$players		= Player::find('user_id = ' . $u->id, [
			'skip_after_assign'	=> true,
			'limit'				=> $start . ', ' . $items_per_page,
			'reorder'			=> 'id asc'
		]);

		// Doações
		$donates	= StarPurchase::find('user_id = ' . $u->id);
		$logins		= UserLogin::find('user_id = ' . $u->id);

		// Países
		$countries	= Country::all();

		// Banimentos
		$banishments	= Banishment::find("type = 'user' and user_id = " . $u->id, [
			'reorder'	=> 'created_at desc'
		]);

		$this->assign('u',				$u);
		$this->assign('page',			$page);
		$this->assign('pages',			$pages);
		$this->assign('logins',			$logins);
		$this->assign('players',		$players);
		$this->assign('donates',		$donates);
		$this->assign('countries',		$countries);
		$this->assign('banishments',	$banishments);
	}

	public function create() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;
		$errors					= [];

		if (!isset($_POST['name']) || !$_POST['name']) {
			$errors[]	= t('users.join.validators.name');
		}

		if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
			$errors[]	= t('users.join.validators.email');
		} elseif (User::find_first('email = "' . addslashes($_POST['email']) . '"', [ 'skip_after_assign' => true ])) {
			$errors[]	= t('users.join.validators.email_exists');
		}

		if (!isset($_POST['country']) || !Country::includes($_POST['country'])) {
			$errors[]	= t('users.join.validators.country');
		}

		if (!isset($_POST['gender']) || !in_array($_POST['gender'], [1, 2])) {
			$errors[]	= t('users.join.validators.gender');
		}

		if (!sizeof($errors)) {
			$password				= random_str(8);

			$user					= new User();
			$user->name				= $_POST['name'];
			$user->email			= $_POST['email'];
			$user->gender			= $_POST['gender'];
			$user->country_id		= $_POST['country'];
			$user->password			= $password;
			$user->user_key			= uniqid(uniqid(), true);
			$user->activation_key	= uniqid(uniqid(), true);
			$user->save();

			UserMailer::dispatch('send_join', [ $user, $password ]);

			$this->json->success	= true;
			$this->json->message	= 'Conta criada com sucesso!';
			$this->json->redirect	= 'admin/users/view/' . $user->id;
		} else {
			$this->json->errors		= $errors;
		}
	}

	public function login() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$user		= User::get_instance();
		$errors		= [];

		if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
			$errors[]	= 'Dados inválidos!';
		} else {
			$u	= User::find_first('id = ' . $_POST['id']);
			if (!$u) {
				$errors[]	= 'Conta inexistente!';
			} else {
				if ($u->hasBanishment() && $user->admin < 2) {
					$errors[]	= 'Você não pode acessar uma conta banida!';
				}

				if ($u->admin && $user->admin < 3) {
					$errors[]	= 'Você não pode acessar a conta de outro staff!';
				} else {
					if ($user->admin < $u->admin) {
						$errors[]	= 'Você não pode acessar a conta de um superior!';
					}
				}
			}
		}

		if (!sizeof($errors)) {
			// Adiciona um log deste acesso
			$login				= new UserLogin();
			$login->admin_id	= $user->id;
			$login->user_id		= $u->id;
			$login->ip			= getIP();
			$login->user_agent	= $_SERVER['HTTP_USER_AGENT'];
			if ($login->save()) {
				// Universal
				$_SESSION['universal']		= true;

				// Salva a sessão antiga
				$_SESSION['orig_user_id']	= $user->id;

				// Seta a sessão nova
				$_SESSION['user_id']		= $u->id;

				$this->json->success	= true;
				$this->json->message	= 'Acesso realizado com sucesso!';
				$this->json->redirect	= 'characters/select';
			}
		} else {
			$this->json->errors		= $errors;
		}
	}

	public function logout() {

	}

	public function cancel_ban() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$user		= User::get_instance();
		$errors		= [];

		if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
			$errors[]	= 'Dados inválidos!';
		} else {
			$banishment	= Banishment::find_first('id = ' . $_POST['id']);
			if (!$banishment) {
				$errors[]	= 'Banimento não encontrado!';
			} else {
				$valid	= between(now(), strtotime($banishment->created_at), strtotime($banishment->finishes_at));
				if (!$valid) {
					$errors[]	= 'O banimento ja foi cumprido!';
				} else {
					$admin	= $banishment->admin();
					if ($banishment->admin_id != $user->id && $user->admin < 3) {
						$errors[]	= 'Você não pode revogar banimento de outro membro da staff!';
					}
				}
			}
		}

		if (!sizeof($errors)) {
			$banishment->finishes_at	= now(true);
			$banishment->save();

			$this->json->success	= true;
			$this->json->message	= 'Banimento revogado com sucesso!';
			$this->json->redirect	= 'admin/users/view/' . $banishment->user_id;
		} else {
			$this->json->errors		= $errors;
		}
	}

	public function add_ban() {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;

		$user		= User::get_instance();
		$errors		= [];

		if (!isset($_POST['user']) || !is_numeric($_POST['user'])) {
			$errors[]	= 'Dados inválidos!';
		} else {
			if (!isset($_POST['date_end']) || !isset($_POST['reason'])) {
				$errors[]	= 'Preencha todos os campos!';
			}

			if (isset($_POST['reason']) && empty($_POST['reason'])) {
				$errors[]	= 'Você deve informar a motivo do banimento!';
			}

			if (isset($_POST['date_end']) && empty($_POST['date_end'])) {
				$errors[]	= 'Você deve informar o fim do banimento!';
			} else {
				$arr_date	= explode('/', $_POST['date_end']);
				if (!is_array($arr_date)) {
					$errors[]	= 'Informe uma data válida!';
				} else {
					list($d, $m, $y)	= $arr_date;
					$date_end	= $y . '-' . $m . '-' . $d;
					if ($date_end < date('Y-m-d', strtotime('+1 day'))) {
						$errors[]	= 'O banimento deve ser de no minimo 1 dia!';
					}

					if ($date_end > date('Y-m-d', strtotime('+15 years'))) {
						$errors[]	= 'O banimento deve ser de no máximo 15 anos!';
					}
				}
			}

			if (!sizeof($errors)) {
				$u	= User::find_first('id = ' . $_POST['user']);
				if (!$u) {
					$errors[]	= 'Conta inexistente!';
				} else {
					if ($u->hasBanishment()) {
						$errors[]	= 'Essa cota ja tem um banimento em andamento!';
					} else {
						if ($u->admin && $user->admin < 3) {
							$errors[]	= 'Você não pode banir outro membro da staff!';
						} else {
							if ($user->admin < $u->admin) {
								$errors[]	= 'Você não pode banir a conta de um superior!';
							}
						}
					}
				}
			}
		}

		if (!sizeof($errors)) {
			$banishment	= new Banishment();
			$banishment->type			= 'user';
			$banishment->admin_id		= $user->id;
			$banishment->user_id		= $u->id;
			$banishment->reason			= $_POST['reason'];
			$banishment->finishes_at	= $date_end . ' ' . date('H:i:s');
			if ($banishment->save()) {
				$this->json->success	= true;
				$this->json->message	= 'Banimento realizado com sucesso!';
				$this->json->redirect	= 'admin/users/view/' . $u->id;
			}
		} else {
			$this->json->errors		= $errors;
		}
	}

	public function edit($id) {

	}
}
