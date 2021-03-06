<?php
	class PlayerItemAttribute extends Relation {
		function name() {
			$name			= '';
			$maxs			= [];
			$max_attributes	= [];
			$groups			= [
				1	=> ['for_atk', 'for_def', 'for_crit', 'for_abs','for_init','for_inc_crit','for_inc_abs']
			];

			foreach($groups as $key => $group) {
				$maxs[$key]				= 0;
				$max_attributes[$key]	= '';

				foreach ($group as $attribute) {
					if($this->$attribute > $maxs[$key]) {
						$maxs[$key]				= $this->$attribute;
						$max_attributes[$key]	= $attribute;
					}
				}
			}

			foreach($maxs as $k => $max) {
				if ($max) {
					$name	.= t('equipments.names.' . $max_attributes[$k]) . ' ';
				}
			}

			return $name;
		}
	}