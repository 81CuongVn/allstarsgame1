<?php
	class HomeController extends Controller {
		function index() {
		}
		function maintenance() {
			$this->layout	= 'maintenance';
		}
		function news_list($page = 1) {
			$items_per_page	= 5;
			$this->layout	= false;
			
			$this->assign('news', SiteNew::find("round = 3", [
				'limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page,
				'reorder' => 'id DESC'
			]));
		}
		function rank_list($page = 1, $type) {
			$items_per_page	= 10;
			$this->layout	= false;
			if ($type == "accounts") {
				$this->assign("type", $type);
				$this->assign('players', RankingAccount::all(['limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page, 'reorder' => 'position_general ASC']));
			} elseif ($type == "players") {
				$this->assign("type", $type);
				$this->assign('players', RankingPlayer::all(['limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page, 'reorder' => 'position_general ASC']));	
			} elseif ($type == "achievements") {
				$this->assign("type", $type);
				$this->assign('players', RankingAchievement::all(['limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page, 'reorder' => 'position_general ASC']));
			} elseif ($type == "organizations") {
				$this->assign("type", $type);
				$this->assign('players', RankingOrganization::all(['limit' => ($items_per_page * ($page - 1)) . ', ' . $items_per_page, 'reorder' => 'position_general ASC']));
			}
		}
		function top_list($page = 1) {
			$items_per_page	= 10;
			$this->layout	= false;
			
			$this->assign('tops', RankingPlayer::find('position_anime=1', [
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

			$this->assign('new', $new);
			$this->assign('comments', $new->comments());
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