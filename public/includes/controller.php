<?php
class Controller {
	private $assigns	= [];
	public	$as_json	= false;
	public	$json		= null;
	public	$view		= null;
	public	$layout		= null;

	function __construct() {
		$this->json	= new stdClass();

		$this->assigns['fb_url'] = "https://www.facebook.com/dialog/oauth?client_id=" . FB_APP_ID . "&redirect_uri=" . urlencode(make_url(FB_CALLBACK_URL)) . "&scope=email";
	}

	function assign($key, $value) {
		$this->assigns[$key]	= $value;
	}

	function get_assignments() {
		return $this->assigns;
	}
}
