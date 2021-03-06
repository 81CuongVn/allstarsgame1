<?php
	class SupportTicketReply extends Relation {
		function user() {
			return User::find($this->user_id);
		}

		function player() {
			return Player::find($this->player_id);
		}

		function ticket() {
			return SupportTicket::find($this->support_ticket_id);
		}

		function uploads() {
			return SUpportTicketUpload::find('support_ticket_id=' . $this->support_ticket_id . ' AND support_ticket_reply_id=' . $this->id);
		}
	}