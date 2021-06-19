<?php
class UserLogin extends Relation {
	function user() {
		return User::find_first('id = ' . $this->user_id);
	}
}
