<?php
class InternalController extends Controller {
	function not_found() {
		$this->render	= '404';
	}

	function denied() {
		$this->render	= '403';
	}

	function maintenance() {
		$this->layout	= false;
		$this->render	= 'maintenance';
	}
}