<?php
class HomeController extends Controller {
	function index() {
		$leagues	= Ranked::find('started = 1 order by league desc');
		$this->assign("leagues",	$leagues);
	}
	function maintenance() {
		$this->layout	= 'maintenance';
	}
	function news_list($page = 1) {
		$this->layout	= FALSE;

		$items_per_page	= 5;
		$total_pages	= SiteNew::find("round='r1'");
		$total_pages	= ceil(sizeof($total_pages) / $items_per_page);
		$page			= (!is_numeric($page) || $page <= 0) ? 1 : $page;
		$page			= ($page > $total_pages) ? $total_pages : $page;
		$start			= ceil(($page * $items_per_page) - $items_per_page);
		$start			= $start < 0 ? 0 : $start;
		$news			= SiteNew::find("round='r1'",[
			'limit'		=> $start . ', ' . $items_per_page,
			'reorder'	=> 'id desc'
		]);

		$this->assign('news',	$news);
	}
	function league_list($league) {
		$this->layout	= false;
		if (is_numeric($league) && $league > 0) {
			$ranked_rankings = RankingRanked::find("league_id={$league} order by position_general asc limit 3");
		} else {
			$ranked_rankings = [];
		}

		$this->assign("ranked_rankings", $ranked_rankings);
	}
	function rank_list($page = 1, $type = '') {
		$this->layout	= FALSE;

		$list			= [];
		$items_per_page	= 10;
		if ($type == "accounts") {
			$list	= RankingAccount::all([
				'limit'		=> ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
				'reorder'	=> 'position_general asc'
			]);
		} elseif ($type == "players") {
			$list	= RankingPlayer::all([
				'limit'		=> ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
				'reorder'	=> 'position_general asc'
			]);
		} elseif ($type == "achievements") {
			$list	= RankingAchievement::all([
				'limit'		=> ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
				'reorder'	=> 'position_general asc'
			]);
		} elseif ($type == "guilds") {
			$list	= RankingGuild::all([
				'limit'		=> ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
				'reorder'	=> 'position_general asc'
			]);
		}

		$this->assign("type",		$type);
		$this->assign('players',	$list);
	}
	function top_list($page = 1) {
		$this->layout	= false;

		$items_per_page	= 10;
		$this->assign('tops',		RankingPlayer::find('position_anime=1', [
			'reorder'	=> 'score desc',
			'limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page
		]));
	}
	function statistic_list($page = 1) {
		$items_per_page	= 10;
		$this->layout	= false;

		$this->assign('players', StatisticPlayer::all([
			'limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
			'reorder' => 'total DESC'
		]));
	}
	function read_news($id = NULL){
		$new	= SiteNew::find_first($id);
		if (!$new)
			redirect_to('home');

		$this->assign('new',		$new);
		$this->assign('comments',	$new->comments());
	}

	function make_comment($news_id = NULL) {
		$this->layout	= false;

		if(is_numeric($news_id)) {
			if (isset($_POST['content']) && trim($_POST['content']) && $_SESSION['user_id']) {
				$comment				= new SiteNewsComment();
				$comment->user_id		= User::get_instance()->id;
				$comment->site_news_id	= $news_id;

				if($_SESSION['player_id']) {
					$comment->player_id	= $_SESSION['player_id'];
				}

				$comment->content	= htmlspecialchars(trim($_POST['content']));
				$comment->save();
			}

			$comments	= SiteNewsComment::find('site_news_id=' . $news_id);
		} else {
			$comments	= [];
		}

		$this->assign('comments', $comments);
	}

	function comment_admin($comment_id = null) {

	}
}
