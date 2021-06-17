<?php
	class SupportController extends Controller {
		function __construct() {
			parent::__construct();

			$this->allowed_exts	= array('png', 'jpg', 'bmp');
			$this->allowed_types	= array('image/png', 'image/jpeg', 'images/jpg', 'image/bmp');
		}

		function index() {
			$this->assign('categories', SupportTicketCategory::all());
			$this->assign('statuses', SupportTicketStatus::all());
		}

		function search() {
			$this->layout	= false;
			$where			= '';
			$limit			= 20;
			$page			= isset($_POST['page']) && is_numeric($_POST['page']) ? $_POST['page'] : 0;

			if ($_SESSION['universal']) {
				if (isset($_POST['id']) && is_numeric($_POST['id'])) {
					$where	.= ' AND id=' . $_POST['id'];
				}

				if (isset($_POST['category']) && is_numeric($_POST['category'])) {
					$where	.= ' AND support_ticket_category_id=' . $_POST['category'];
				}

				if (isset($_POST['status']) && is_numeric($_POST['status'])) {
					$where	.= ' AND support_ticket_status_id=' . $_POST['status'];
				}

				if (isset($_POST['title']) && $_POST['title']) {
					$where	.= ' AND title LIKE "%' . $_POST['title'] . '%"';
				}
			} else {
				$where	.= ' AND user_id=' . $_SESSION['user_id'];
			}

			$result	= SupportTicket::filter($where, $page, $limit);

			$this->assign('tickets', $result['tickets']);
			$this->assign('pages', $result['pages']);
			$this->assign('page', $page);
			$this->assign('player', Player::get_instance());
		}

		function open() {
			$errors	= [];

			if ($_POST) {
				if(!isset($_POST['category']) || (isset($_POST['category'])) && !is_numeric($_POST['category'])) {
					$errors[]	= t('support.open.errors.category');
				}

				if(!isset($_POST['title']) || (isset($_POST['title'])) && !$_POST['title']) {
					$errors[]	= t('support.open.errors.title');
				}

				if(!isset($_POST['description']) || (isset($_POST['description'])) && !$_POST['description']) {
					$errors[]	= t('support.open.errors.description');
				}

				if(isset($_FILES['attachments'])) {
					for($f = 0; $f < sizeof($_FILES['attachments']['name']); $f++) {
						if(!$_FILES['attachments']['error'][$f] && $_FILES['attachments']['name'][$f]) {
							$ext	= substr($_FILES['attachments']['name'][$f], -3, 3);
							$type	= $_FILES['attachments']['type'][$f];

							if(!in_array($ext, $this->allowed_exts)) {
								$errors[]	= t('support.open.errors.invalid_extension');
							}

							if(!in_array($type, $this->allowed_types)) {
								$errors[]	= t('support.open.errors.invalid_type');
							}
						}
					}
				}

				if(!sizeof($errors)) {
					$ticket	= new SupportTicket();
					$ticket->user_id	= $_SESSION['user_id'];

					if($_SESSION['player_id']) {
						$ticket->player_id	= $_SESSION['player_id'];
					}

					$ticket->title						= htmlspecialchars($_POST['title']);
					$ticket->content					= htmlspecialchars($_POST['description']);
					$ticket->support_ticket_category_id	= $_POST['category'];
					$ticket->support_ticket_status_id	= 1;
					$ticket->user_agent					= isset($_POST['same_browser']) ? $_SERVER['HTTP_USER_AGENT'] : $_POST['browser'];
					$ticket->save();

					for($f = 0; $f < sizeof($_FILES['attachments']['name']); $f++) {
						if(!$_FILES['attachments']['error'][$f] && $_FILES['attachments']['name'][$f]) {
							$ext	= substr($_FILES['attachments']['name'][$f], -3, 3);
							$id		= uniqid('', true);

							move_uploaded_file($_FILES['attachments']['tmp_name'][$f], ROOT . '/uploads/support/' . $id . '.' . $ext);

							$upload						= new SupportTicketUpload();
							$upload->support_ticket_id	= $ticket->id;
							$upload->user_id			= $_SESSION['user_id'];
							$upload->filename			= $id . '.' . $ext;

							if($_SESSION['player_id']) {
								$upload->player_id	= $_SESSION['player_id'];
							}

							$upload->save();
						}
					}

					redirect_to('support');
					return false;
				} else {
					$this->assign('browser', isset($_POST['browser']) ? $_POST['browser'] : '');
					$this->assign('title', isset($_POST['title']) ? $_POST['title'] : '');
					$this->assign('category', isset($_POST['category']) ? $_POST['category'] : 1);
					$this->assign('description', isset($_POST['description']) ? $_POST['description'] : '');
				}
			} else {
				$this->assign('browser', '');
				$this->assign('title', '');
				$this->assign('description', '');
				$this->assign('category', 1);
			}

			$this->assign('categories', SupportTicketCategory::all());
			$this->assign('statuses', SupportTicketStatus::all());
			$this->assign('errors', $errors);
		}

		function ticket($id = null) {
			if(is_numeric($id)) {
				$ticket			= SupportTicket::find($id);

				if(!$_SESSION['universal'] && $ticket->user_id != $_SESSION['user_id']) {
					$this->denied	= true;
					return;
				}

				$ticket_user	= $ticket->user();
				$ticket_player	= $ticket->player_id ? $ticket->player() : false;

				$this->assign('ticket', $ticket);
				$this->assign('ticket_user', $ticket_user);
				$this->assign('ticket_player', $ticket_player);
				$this->assign('attachments', SupportTicketUpload::find('support_ticket_id=' . $ticket->id . ' AND support_ticket_reply_id=0'));
				$this->assign('replies', $ticket->replies());
			} else {
				$this->denied	= true;
			}
		}

		function reply($id = null) {
			if(is_numeric($id)) {
				$ticket	= SupportTicket::find($id);

				if(!$_SESSION['universal'] && $ticket->user_id != $_SESSION['user_id']) {
					$this->denied	= true;
					return;
				}

				if($ticket->support_ticket_status_id == 4) {
					$this->denied	= true;
					return;
				}

				$reply						= new SupportTicketReply();
				$reply->user_id				= $_SESSION['user_id'];
				$reply->support_ticket_id	= $id;
				$reply->content				= htmlspecialchars(trim($_POST['content']));

				if($_SESSION['universal']) {
					$ticket->support_ticket_status_id	= $_POST['close'] ? 4 : 3;
				} else {
					$ticket->support_ticket_status_id	= 2;
				}

				$ticket->last_replied_id	= $_SESSION['user_id'];
				$ticket->last_replied_at	= now(true);

				if($_SESSION['player_id']) {
					$reply->player_id	= $_SESSION['player_id'];
				}

				$ticket->save();
				$reply->save();

				if(isset($_FILES['attachments'])) {
					for($f = 0; $f < sizeof($_FILES['attachments']['name']); $f++) {
						if(!$_FILES['attachments']['error'][$f] && $_FILES['attachments']['name'][$f]) {
							$ext		= substr($_FILES['attachments']['name'][$f], -3, 3);
							$type		= $_FILES['attachments']['type'][$f];
							$error		= false;
							$upload_id	= uniqid('', true);

							if(!in_array($ext, $this->allowed_exts)) {
								$error	= true;
							}

							if(!in_array($type, $this->allowed_types)) {
								$error	= true;
							}

							if (!$error) {
								move_uploaded_file($_FILES['attachments']['tmp_name'][$f], ROOT . '/uploads/support/' . $upload_id . '.' . $ext);

								$upload								= new SupportTicketUpload();
								$upload->support_ticket_id			= $ticket->id;
								$upload->support_ticket_reply_id	= $reply->id;
								$upload->user_id					= $_SESSION['user_id'];
								$upload->filename					= $upload_id . '.' . $ext;

								if($_SESSION['player_id']) {
									$upload->player_id	= $_SESSION['player_id'];
								}

								$upload->save();
							}
						}
					}
				}

				redirect_to('support#ticket/' . $id);
			} else {
				$this->denied	= true;
			}
		}

		function reopen($id) {
			if(!is_numeric($id) || (is_numeric($id) && !$_SESSION['universal'])) {
				$this->denied	= true;
				return;
			}

			$ticket								= SupportTicket::find($id);
			$ticket->support_ticket_status_id	= 5;
			$ticket->save();

			$this->as_json			= true;
			$this->json->success	= true;
		}

		function alternate($full = false) {
			$this->layout   = FALSE;
			$this->render   = FALSE;

			if(!$_SESSION['universal']) {
				$this->denied	= true;
				return;
			}

			$ticket	= SupportTicket::find($_POST['ticket']);

			$_SESSION['orig_player_id']	= $_SESSION['player_id'];
			$_SESSION['orig_user_id']	= $_SESSION['user_id'];
			$_SESSION['orig_ticket_id']	= $ticket->id;

			$_SESSION['user_id']		= $ticket->user_id;

			if($full && $ticket->player_id) {
				$_SESSION['player_id']	= $ticket->player_id;
			} else {
				$_SESSION['player_id']	= 0;
			}
		}

		function revert() {
			if(!$_SESSION['universal']) {
				$this->denied	= true;
				return;
			}

			$_SESSION['player_id']		= $_SESSION['orig_player_id'];
			$_SESSION['user_id']		= $_SESSION['orig_user_id'];
			$ticket						= $_SESSION['orig_ticket_id'];

			$_SESSION['orig_user_id']	= 0;
			$_SESSION['orig_player_id']	= 0;
			$_SESSION['orig_ticket_id']	= 0;

			redirect_to('support#ticket/' . $ticket);
		}
	}
