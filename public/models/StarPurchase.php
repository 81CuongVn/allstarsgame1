<?php
class StarPurchase extends Relation {
	function salt_encrypt($v, $key) {
		$iv = substr(md5($key), 0, 16);
		return openssl_encrypt($v, 'AES-256-CBC', md5($key),0, $iv);
	}

	function plan() {
		return StarPlan::find_first('id = ' . $this->star_plan_id);
	}
}
