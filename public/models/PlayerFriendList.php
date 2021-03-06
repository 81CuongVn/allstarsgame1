<?php
	class PlayerFriendList extends Relation {
		function limit_by_day($id) {
			return PlayerFriendList::find('player_id=' . $id . ' AND DATE(created_at) = DATE(NOW())');
		}
	}