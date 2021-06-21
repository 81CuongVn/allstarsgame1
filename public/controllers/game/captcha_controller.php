<?php
class CaptchaController extends Controller {
	function __construct() {
		$this->render	= false;
		$this->layout	= false;

		parent::__construct();
	}

	function login() {
		$img				= new Captcha();
		$img->ssid			= "captcha_login";
		$img->image_width	= 30;
		$img->image_height	= 15;

		$img->text_maximum_distance = 10;
		$img->text_minimum_distance = 10;
		$img->draw_lines			= false;
		$img->draw_lines_over_text	= false;
		$img->arc_linethroug		= false;
		$img->use_wordlist			= false;

		$img->text_angle_minimum	= 0;
		$img->text_angle_maximum	= 0;
		$img->text_x_start			= 0;
		$img->image_bg_color		= '#2b2724';

		$img->code_length			= 3;
		$img->ttf_file				= ROOT . "/assets/fonts/verdana.ttf";
		$img->font_size				= 10;

		$img->show();
	}
}
