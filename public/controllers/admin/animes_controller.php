<?php
class AnimesController extends Controller {
	public function index() {
		$page			= !isset($_GET['page']) || !is_numeric($_GET['page']) ? 1 : $_GET['page'];
		$items_per_page	= 6;
		$all_animes		= Recordset::query("SELECT COUNT(id) AS total FROM animes")->row()->total;
		$pages			= ceil($all_animes / $items_per_page);
		$page			= (!is_numeric($page) || $page <= 0) ? 1 : $page;
		$page			= ($page > $pages) ? $pages : $page;
		$start			= ceil(($page * $items_per_page) - $items_per_page);
		$start			= $start < 0 ? 0 : $start;
		$animes			= Anime::all([
			'limit'		=> $start . ', ' . $items_per_page,
			'reorder'	=> 'id asc'
		]);

		$this->assign('page',		$page);
		$this->assign('pages',		$pages);
		$this->assign('animes',		$animes);
	}
}
