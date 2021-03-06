<?php
	class SkipTurnItem {
		public	$is_turn_skip		= true;
		public	$is_buff			= false;
		public	$is_defensive		= false;
		public	$item_type_id		= 1;
		public	$id					= 0;
		public	$cooldown			= 0;
		public	$item_effect_ids	= null;

		function formula() {
			$formula	= new stdClass();
			$formula->hit_chance	= 100;
			$formula->defense		= 0;
			$formula->damage		= 0;
			$formula->consume_mana	= 0;
			$formula->cooldown		= 0;
			$formula->attack_speed	= 0;

			return $formula;
		}

		function attack_type() {
			return false;
		}

		function is_weak_to() {
			return false;
		}

		function is_strong_to() {
			return false;
		}

		function description() {
			$description						= new stdClass();
			$description->name					= 'Pular turno';
			$description->description			= 'Pular turno';
			$description->item_attack_type_id	= 0;

			return $description;
		}

		function technique_tooltip() {
			return '';
		}

		function set_player() {}
		function set_player_item() {}
		function force_attack_type() {}
	}