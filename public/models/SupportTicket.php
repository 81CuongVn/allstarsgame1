<?php
	class SupportTicket extends Relation {
		function user() {
			return User::find($this->user_id);
		}

		function player() {
			return Player::find($this->player_id);
		}

		function replies() {
			return SupportTicketReply::find('support_ticket_id=' . $this->id);
		}

		function category() {
			return SupportTicketCategory::find($this->support_ticket_category_id, ['cache' => true]);
		}

		function status() {
			return SupportTicketStatus::find($this->support_ticket_status_id, ['cache' => true]);
		}

		static function filter($where, $page, $limit) {
			$result	= [];

			if(!$where) {
				$result['pages']	= ceil(Recordset::query('SELECT MAX(id) AS _max FROM support_tickets')->row()->_max / $limit);
				$result['tickets']	= SupportTicket::all(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'id DESC']);
			} else {
				$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM support_tickets WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
				$result['tickets']	= SupportTicket::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'id DESC']);
			}

			return $result;
		}
	}