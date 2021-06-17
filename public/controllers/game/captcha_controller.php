<?php
	class CaptchaController extends Controller {
		function __construct() {
			$this->render	= false;
			$this->layout	= false;

			parent::__construct();
		}

		function join() {
			$img				= new Captcha();
			$img->ssid			= "captcha_join";
			$img->image_width	= 120;
			$img->image_height	= 40;

			$img->text_maximum_distance = $img->text_minimum_distance = 22;
			$img->draw_lines			= true;
			$img->line_color			= "#FFFFFF";
			$img->draw_lines_over_text	= false;
			$img->arc_linethroug		= false;
			$img->use_wordlist			= false;
			$img->code_length			= 5;
			$img->ttf_file				= ROOT . "/assets/fonts/verdana.ttf";
			$img->font_size				= 16;

			$img->show();
		}

		function reset_password() {
			$img				= new Captcha();
			$img->ssid			= "captcha_reset";
			$img->image_width	= 120;
			$img->image_height	= 40;

			$img->text_maximum_distance = $img->text_minimum_distance = 22;
			$img->draw_lines			= true;
			$img->line_color			= "#FFFFFF";
			$img->draw_lines_over_text	= false;
			$img->arc_linethroug		= false;
			$img->use_wordlist			= false;
			$img->code_length			= 5;
			$img->ttf_file				= ROOT . "/assets/fonts/verdana.ttf";
			$img->font_size				= 16;

			$img->show();
		}

		function login() {
			$img				= new Captcha();
			$img->ssid			= "captcha_login";
			$img->image_width	= 30;
			$img->image_height	= 15;

			$img->text_maximum_distance = $img->text_minimum_distance = 10;
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

		function beta_login() {
			$img				= new Captcha();
			$img->ssid			= "captcha_beta_login";
			$img->image_width	= 120;
			$img->image_height	= 40;

			$img->text_maximum_distance = $img->text_minimum_distance = 22;
			$img->draw_lines			= true;
			$img->line_color			= "#FFFFFF";
			$img->draw_lines_over_text	= false;
			$img->arc_linethroug		= false;
			$img->use_wordlist			= false;
			$img->code_length			= 5;
			$img->ttf_file				= ROOT . "/assets/fonts/verdana.ttf";
			$img->font_size				= 16;

			$img->show();
		}
	}
