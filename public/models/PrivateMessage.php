<?php
class PrivateMessage extends Relation {
	static	$paranoid	= true;

	function from() {
		if ($this->from_id == Player::get_instance()->id) {
			return Player::get_instance();
		} else {
			return Player::find($this->from_id);
		}
	}

	function to() {
		if ($this->to_id == Player::get_instance()->id) {
			return Player::get_instance();
		} else {
			return Player::find($this->to_id);
		}
	}

	function reply() {
		return PrivateMessage::find_first('reply_id=' . $this->id);
	}

	function filter($where, $page, $limit) {
		$result	= [];

		if(!$where) {
			$result['pages']	= floor(Recordset::query('SELECT MAX(id) AS _max FROM private_messages')->row()->_max / $limit);
			$result['messages']	= PrivateMessage::find(['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'created_at DESC']);
		} else {
			$result['pages']	= ceil(Recordset::query('SELECT COUNT(id) AS _max FROM (SELECT id FROM private_messages WHERE 1=1 ' . $where . ') _w')->row()->_max / $limit);
			$result['messages']	= PrivateMessage::find('1=1 ' . $where, ['limit' => ($page * $limit) . ', ' . $limit, 'reorder' => 'created_at DESC']);
		}

		return $result;
	}
}
