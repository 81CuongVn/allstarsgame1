<?php
class StarPurchase extends Relation {
	function salt_encrypt($v, $key) {
		$iv = substr(md5($key), 0, 16);
		return openssl_encrypt($v, 'AES-256-CBC', md5($key),0, $iv);
	}

	function user() {
		return User::find_first('id = ' . $this->user_id);
	}

	function admin() {
		return User::find_first('id = ' . $this->transid);
	}

	function plan() {
		return StarPlan::find_first('id = ' . $this->star_plan_id);
	}

	function isDouble() {
		return StarDouble::find_first("'{$this->created_at}' BETWEEN data_init AND data_end");
	}
}
