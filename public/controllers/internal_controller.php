<?php
class InternalController extends Controller {
	function not_found() {
		$this->render	= '404';
	}

	function denied() {
		$this->render	= '403';
	}
}
