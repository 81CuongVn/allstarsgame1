<?php
class ArticlesController extends Controller {
	public function index() {
		$page			= !isset($_GET['page']) || !is_numeric($_GET['page']) ? 1 : $_GET['page'];
		$items_per_page	= 6;
		$all_articles	= Recordset::query("SELECT COUNT(id) AS total FROM site_news")->row()->total;
		$pages			= ceil($all_articles / $items_per_page);
		$page			= (!is_numeric($page) || $page <= 0) ? 1 : $page;
		$page			= ($page > $pages) ? $pages : $page;
		$start			= ceil(($page * $items_per_page) - $items_per_page);
		$start			= $start < 0 ? 0 : $start;
		$articles			= SiteNew::all([
			'limit'		=> $start . ', ' . $items_per_page,
			'reorder'	=> 'created_at desc'
		]);

		$this->assign('page',		$page);
		$this->assign('pages',		$pages);
		$this->assign('articles',	$articles);
	}

	public function delete($id) {
		$this->layout			= false;
		$this->as_json			= true;
		$this->render			= false;
		$this->json->success	= false;
		$errors					= [];

		if (!$id) {
			$errors[]	= 'Noticia inexistente!';
		} else {
			$user		= User::get_instance();
			$article	= SiteNew::find($id);

			if ($article->user_id != $user->id && $user->admin < 2) {
				$errors[]	= 'Você só pode apagar as noticias publicadas por você!';
			}
		}

		if (!sizeof($errors)) {
			$this->json->success	= true;
			$this->json->message	= 'Noticia removida com sucesso!';
			$this->json->redirect	= 'admin/articles';

			$article->destroy();
		} else {
			$this->json->errors		= $errors;
		}
	}

	public function edit($id) {
		if ($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= [];

			if (!$id) {
				$errors[]	= 'Noticia inexistente!';
			} else {
				$article	= SiteNew::find($id);
				$user		= User::get_instance();

				if (!isset($_POST['title']) || !isset($_POST['type']) || !isset($_POST['description'])) {
					$errors[]	= 'Preencha todos os campos!';
				}

				if (!in_array($_POST['type'], [ 'news', 'promotions', 'events', 'maintenance' ])) {
					$errors[]	= 'Escolha uma categoria váliida!';
				}

				if (strlen($_POST['title']) < 10 || strlen($_POST['title']) > 30) {
					$errors[]	= 'O título deve ter entre 10 e 30 caracteres!';
				}

				if (strlen($_POST['description']) < 10) {
					$errors[]	= 'A notícia é muito curta!';
				}
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;
				$this->json->message	= 'Noticia modificada com sucesso!';
				$this->json->redirect	= 'admin/articles/edit/' . $article->id;

				$article->title			= $_POST['title'];
				$article->type			= $_POST['type'];
				$article->description	= $_POST['description'];
				$article->save();
			} else {
				$this->json->errors		= $errors;
			}
		} else {
			if (!$id) {
				redirect_to('addmin/articles');
				exit;
			}

			$article	= SiteNew::find($id);
			$this->assign('article',	$article);
		}
	}

	public function create() {
		$user	= User::get_instance();

		if ($_POST) {
			$this->layout			= false;
			$this->as_json			= true;
			$this->render			= false;
			$this->json->success	= false;
			$errors					= [];

			if (!isset($_POST['title']) || !isset($_POST['type']) || !isset($_POST['description'])) {
				$errors[]	= 'Preencha todos os campos!';
			}

			if (!in_array($_POST['type'], [ 'news', 'promotions', 'events', 'maintenance' ])) {
				$errors[]	= 'Escolha uma categoria váliida!';
			}

			if (strlen($_POST['title']) < 10 || strlen($_POST['title']) > 30) {
				$errors[]	= 'O título deve ter entre 10 e 30 caracteres!';
			}

			if (strlen($_POST['description']) < 10) {
				$errors[]	= 'A notícia é muito curta!';
			}

			if (!sizeof($errors)) {
				$this->json->success	= true;
				$this->json->message	= 'Noticia adicionada com sucesso!';
				$this->json->redirect	= 'admin/articles';

				$insert					= new SiteNew();
				$insert->user_id		= $user->id;
				$insert->title			= $_POST['title'];
				$insert->type			= $_POST['type'];
				$insert->description	= $_POST['description'];
				$insert->save();
			} else {
				$this->json->errors		= $errors;
			}
		}
	}
}
