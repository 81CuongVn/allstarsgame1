<?php
	trait BattleTechniqueLocks {
		function get_technique_locks() {
			return SharedStore::G($this->build_technique_lock_uid(), []);
		}

		function add_technique_lock($instance, $cooldown = null) {
			$locks					= SharedStore::G($this->build_technique_lock_uid(), []);
			$locks[$instance->id]	= [
				'turns'		=> is_null($cooldown) ? $instance->formula()->cooldown : $cooldown,
				'infinity'	=> false
			];

			SharedStore::S($this->build_technique_lock_uid(), $locks);
		}

		function remove_technique_lock($key) {
			$locks	= SharedStore::G($this->build_technique_lock_uid(), []);
			unset($locks[$key]);

			SharedStore::S($this->build_technique_lock_uid(), $locks);
		}

		function has_technique_lock($id) {
			$locks	= SharedStore::G($this->build_technique_lock_uid(), []);

			return in_array($id, array_keys($locks));
		}

		function rotate_technique_locks() {
			$locks		= SharedStore::G($this->build_technique_lock_uid(), []);
			$new_locks	= [];

			foreach ($locks as $key => $lock) {
				if(!$lock['infinity']) {
					$lock['turns']--;
				}

				if($lock['turns'] > 0) {
					$new_locks[$key]	= $lock;
				}
			}

			SharedStore::S($this->build_technique_lock_uid(), $new_locks);
		}

		function clear_technique_locks() {
			SharedStore::S($this->build_technique_lock_uid(), []);
		}

		function add_ability_lock() {
			SharedStore::S($this->build_ability_lock_uid(), [
				'ability'	=> $this->character_ability_id,
				'duration'	=> $this->ability()->cooldown
			]);
		}

		function has_ability_lock() {
			return SharedStore::G($this->build_ability_lock_uid(), false);
		}

		function rotate_ability_lock() {
			if ($this->has_ability_lock()) {
				$lock	= SharedStore::G($this->build_ability_lock_uid());
				$lock['duration']--;

				if ($lock['duration'] <= 0) {
					$this->clear_ability_lock();
				} else {
					SharedStore::S($this->build_ability_lock_uid(), $lock);
				}
			}
		}

		function clear_ability_lock() {
			SharedStore::S($this->build_ability_lock_uid(), null);
		}

		function add_speciality_lock() {
			SharedStore::S($this->build_speciality_lock_uid(), [
				'ability'	=> $this->character_speciality_id,
				'duration'	=> $this->speciality()->cooldown
			]);
		}

		function has_speciality_lock() {
			return SharedStore::G($this->build_speciality_lock_uid(), false);
		}

		function rotate_speciality_lock() {
			if ($this->has_speciality_lock()) {
				$lock	= SharedStore::G($this->build_speciality_lock_uid());
				$lock['duration']--;

				if ($lock['duration'] <= 0) {
					$this->clear_speciality_lock();
				} else {
					SharedStore::S($this->build_speciality_lock_uid(), $lock);
				}
			}
		}

		function clear_speciality_lock() {
			SharedStore::S($this->build_speciality_lock_uid(), null);
		}
	}