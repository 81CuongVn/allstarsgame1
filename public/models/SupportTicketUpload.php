<?php
class SupportTicketUpload extends Relation {
	function user() {
		return User::find($this->user_id);
	}

	function player() {
		return Player::find($this->player_id);
	}

	function ticket() {
		return SupportTicket::find($this->support_ticket_id);
	}

	function comment() {
		return SupportTicketComment::find($this->support_ticket_comment_id);
	}
}
